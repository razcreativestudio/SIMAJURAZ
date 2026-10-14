## [2026-05-24] Fix 500 Error di API HPP
- Mengatasi bug \SyntaxError: Unexpected end of JSON input\ yang disebabkan oleh uncaught PDOException saat melakukan UPDATE di \pi/RAZapiHpp.php\.
- Menambahkan blok \	ry...catch (Throwable \)\ pada fungsi \updateHpp\ dan \pushToInventory\ agar error database (misal schema tidak sinkron atau DB lock) mengembalikan respons JSON (HTTP 500) yang rapi alih-alih melempar Fatal Error.
- Menambahkan auto-migration / silent \ALTER TABLE\ di \updateHpp\ untuk memastikan kolom baru (\portions\, \current_sell_price\, \
otes\) selalu ada pada tabel \hpp_calculations\ sebelum update dijalankan (menghindari error 'no such column').
- Menambahkan instruksi \ini_set('display_errors', 1); error_reporting(E_ALL);\ untuk debugging backend.
- Membuat backup file \pi/RAZapiHpp.php\ ke folder \ackup/RAZapiHpp.php.bak\ sebelum diubah.

