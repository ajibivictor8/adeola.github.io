<?php
header('Content-Type: application/json; charset=utf-8');
$config = require __DIR__ . '/config.php';

function respond($success, $message, $extra = [], $status = 200) {
    http_response_code($status);
    echo json_encode(array_merge(['success'=>$success,'message'=>$message], $extra), JSON_UNESCAPED_SLASHES);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(false, 'POST requests only.', [], 405);
foreach ([$config['upload_dir'], $config['data_dir']] as $dir) {
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) respond(false, 'Server storage directory is unavailable.', [], 500);
    if (!is_writable($dir)) respond(false, 'Server storage directory is not writable.', [], 500);
}

$required = ['surname','first_name','gender','date_of_birth','nationality','state_of_origin','lga','phone','email','permanent_address','next_of_kin_name','next_of_kin_phone','jamb_reg_no','utme_score','mode_of_entry','preferred_faculty','preferred_course','olevel_type','sittings','declaration'];
foreach ($required as $field) {
    if (!isset($_POST[$field]) || trim((string)$_POST[$field]) === '') respond(false, 'Required field missing: '.str_replace('_',' ',$field), [], 422);
}
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) respond(false, 'Invalid email address.', [], 422);
$score = filter_var($_POST['utme_score'], FILTER_VALIDATE_INT);
if ($score === false || $score < 0 || $score > 400) respond(false, 'UTME score must be between 0 and 400.', [], 422);
if (!in_array($_POST['mode_of_entry'], ['UTME','Direct Entry'], true)) respond(false, 'Invalid mode of entry.', [], 422);
if (!in_array($_POST['olevel_type'], ['WAEC','NECO','GCE'], true)) respond(false, 'Invalid O\'Level type.', [], 422);
if (!in_array($_POST['sittings'], ['1','2'], true)) respond(false, 'Invalid number of sittings.', [], 422);
if (empty($_POST['declaration'])) respond(false, 'Declaration must be accepted.', [], 422);
if (!in_array($_POST['preferred_faculty'], $config['faculties'], true)) respond(false, 'Invalid preferred faculty.', [], 422);

for ($i=0;$i<6;$i++) {
    $subject = trim((string)($_POST['subject_'.$i] ?? ''));
    $grade = trim((string)($_POST['grade_'.$i] ?? ''));
    if ($subject === '' || $grade === '') respond(false, 'All O\'Level subject rows must be completed.', [], 422);
    if ($i < 2 && $subject !== ($i === 0 ? 'English Language' : 'Mathematics')) respond(false, 'English Language and Mathematics are required.', [], 422);
    if (!in_array($grade,['A1','B2','B3','C4','C5','C6','D7','E8','F9'],true)) respond(false,'Invalid O\'Level grade.',[],422);
}
if ($_POST['mode_of_entry'] === 'Direct Entry') {
    foreach (['de_qualification','de_institution','de_year','de_result'] as $f) if (trim((string)($_POST[$f] ?? ''))==='') respond(false,'Direct Entry field missing: '.str_replace('_',' ',$f),[],422);
}

function validate_upload($field, $rule) {
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) return [false,'Missing file: '.$field];
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) return [false,'Upload error for '.$field.'.'];
    if ($f['size'] <= 0 || $f['size'] > $rule['max_bytes']) return [false,'File size is invalid for '.$field.'.'];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext,$rule['extensions'],true)) return [false,'File extension is not allowed for '.$field.'.'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!in_array($mime,$rule['mime'],true)) return [false,'File type is not allowed for '.$field.'.'];
    if ($field === 'passport' && @getimagesize($f['tmp_name']) === false) return [false,'Passport must be a valid image.'];
    return [true,''];
}
$stored=[];
$applicationId = 'ISU-'.date('Y').'-'.strtoupper(bin2hex(random_bytes(3)));
foreach ($config['allowed_uploads'] as $field=>$rule) {
    [$ok,$msg]=validate_upload($field,$rule); if (!$ok) respond(false,$msg,[],422);
    $f=$_FILES[$field]; $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
    $safe=$applicationId.'_'.preg_replace('/[^a-z0-9_-]/i','', $field).'.'.$ext;
    $destination=$config['upload_dir'].$safe;
    if (!move_uploaded_file($f['tmp_name'],$destination)) respond(false,'Could not save uploaded file: '.$field,[],500);
    @chmod($destination,0600); $stored[$field]=$safe;
}

$data=$_POST;
unset($data['declaration']);
$data['application_id']=$applicationId; $data['submitted_at']=date('c'); $data['ip_hash']=hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
foreach ($stored as $k=>$v) $data[$k.'_file']=$v;
$csv=$config['data_dir'].'applications.csv';
$headers=array_keys($data);
$exists=file_exists($csv)&&filesize($csv)>0;
$fp=@fopen($csv,'ab');
if (!$fp) respond(false,'Could not save application data.',[],500);
if (flock($fp,LOCK_EX)) {
    if (!$exists) fputcsv($fp,$headers);
    fputcsv($fp,array_map(fn($h)=>$data[$h] ?? '',$headers));
    fflush($fp); flock($fp,LOCK_UN);
} else { fclose($fp); respond(false,'Could not lock application data file.',[],500); }
fclose($fp);

// Email notification is best-effort. The application remains saved if mail() is unavailable.
$subject='New admission application '.$applicationId;
$body="A new application has been submitted.\nApplication ID: $applicationId\nName: ".($_POST['surname'].' '.$_POST['first_name'])."\nEmail: ".($_POST['email']??'')."\nFaculty: ".($_POST['preferred_faculty']??'')."\nCourse: ".($_POST['preferred_course']??'')."\n";
@mail($config['admin_email'],$subject,$body,'From: '.$config['admin_email']);
respond(true,'Application submitted successfully.', ['application_id'=>$applicationId]);
