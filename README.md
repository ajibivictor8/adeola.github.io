# ISU Undergraduate Admission Application Portal

A dependency-free, mobile-first PHP/HTML/CSS/vanilla-JavaScript admission application portal designed for ordinary shared hosting such as InfinityFree.

## Files

- `apply.html` — five-step application form.
- `script.js` — navigation, validation, localStorage, file checks, passport preview and AJAX submission.
- `style.css` — responsive Nigerian university-style design using deep green and white.
- `process.php` — server-side validation, safe file storage, Application ID generation, CSV persistence and best-effort email notification.
- `config.php` — central settings: university name, contact details, admin email, file limits, allowed MIME types/extensions and faculty list.
- `success.html` — success page showing the generated Application ID.
- `index.html` — redirects visitors to the application form.
- `uploads/.htaccess` — prevents public access to uploaded documents.
- `data/.htaccess` — prevents public access to application data.
- `data/applications.csv` — created empty; PHP appends submitted applications here.

## 1. Upload to InfinityFree

1. Create/activate your InfinityFree hosting account and domain/subdomain.
2. Open **File Manager** (or connect using FTP).
3. Open the web root, commonly `htdocs`.
4. Upload **all files and folders in this package** into that web root.
5. Make sure `apply.html`, `process.php`, `config.php`, `script.js`, and `style.css` are in the same directory.
6. Make sure the `uploads/` and `data/` folders are present.

## 2. Create/protect uploads and data folders

The package already includes:

- `uploads/.htaccess`
- `data/.htaccess`

The PHP script also attempts to create the folders if they do not exist.

Recommended permissions are **755** for folders and normal readable PHP/HTML/CSS files. If the host requires it for writing, use the hosting panel's recommended writable permission (sometimes **777** on shared hosting). Avoid 777 unless the host actually requires it.

`uploads/` and `data/` are protected from direct browser access by `.htaccess`.

## 3. Change the admin email

Open `config.php` and edit:

```php
'admin_email' => 'admissions@isuife.edu.ng',
```

Replace it with the address that should receive notifications. Also change `contact_email` and `contact_phone` if the portal should display different contact information.

The default faculty list is based on ISU's public faculty/staff listings and is intentionally stored in `config.php` so it can be changed without editing the form.

## 4. Test the form

1. Open your hosted `apply.html`.
2. Complete Step 1 and continue through Step 5.
3. Test invalid entries intentionally: empty required fields, UTME score above 400, an oversized passport, and an unsupported document type.
4. Select **Direct Entry** and confirm that its extra fields become required.
5. On Step 4, confirm that the passport image previews and that filenames/sizes appear.
6. Accept the declaration and submit.
7. The browser sends the complete form through `FormData` to `process.php`.
8. A successful response redirects to `success.html?ref=ISU-YYYY-XXXXXX`.
9. In File Manager/FTP, verify that the uploaded files exist in `uploads/` and that `data/applications.csv` contains the application record.
10. Check the configured admin inbox for the optional notification email.

## Important hosting settings

The passport limit is 100KB and the other document limit is 5MB. PHP's own upload limits must not be lower than the files you expect to accept. If InfinityFree's PHP configuration is lower than 5MB, use the hosting control panel's PHP configuration options where available or reduce `other_file_max_bytes` in `config.php` to match the server.

## Security notes

- File type is checked using the uploaded file's extension **and** detected MIME type on the server.
- Uploaded files are renamed to generated Application ID-based filenames.
- The upload directory and data directory deny direct web access.
- Application records are written with file locking to reduce concurrent-write problems.
- The user's IP address is not stored directly; a SHA-256 hash is stored for basic duplicate/abuse correlation. Remove the `ip_hash` field from `process.php` if it is not needed.
- The application CSV contains personal information and should remain inaccessible to the public.
- Use HTTPS on the production domain.
- Change the default admin/contact details before production use.

## Data format

Each successful application is appended to `data/applications.csv`. The first row contains column names. Uploaded filenames are stored in columns ending in `_file`.

## Email

`process.php` calls PHP `mail()` after successfully saving the application. The application is **not** discarded if email delivery is unavailable. Always verify actual mail delivery on the deployed host rather than assuming it is enabled.

## Customizing the university

Change these settings in `config.php`:

- `university_name`
- `short_name`
- `admin_email`
- `contact_phone`
- `contact_email`
- `academic_year`
- `faculties`
- file size/type rules

For a different institution, edit the faculty array and contact details in `config.php`. The form itself does not require npm, Composer, a database, or a build step.

## Production checklist

- [ ] Replace the default admin/contact email.
- [ ] Confirm the current admission year and faculty/programme list.
- [ ] Confirm PHP is enabled.
- [ ] Confirm `uploads/` and `data/` are writable by PHP.
- [ ] Confirm `.htaccess` protection works by trying to open a file in each protected directory; it should be denied.
- [ ] Test a complete application and inspect the CSV.
- [ ] Test all four uploads.
- [ ] Test Direct Entry.
- [ ] Test email notification.
- [ ] Back up `data/applications.csv` and `uploads/` securely.

## ISU faculty data note

The included faculty names were checked against ISU's public staff directory and university pages while preparing this package. Admission requirements and programme availability can change, so the institution should verify the list and the actual admission requirements for the specific admission cycle before production deployment.
