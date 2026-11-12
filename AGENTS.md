# Agent Notes (mod/lamslesson)

Only a Moodle plugin: this code assumes it lives at `moodle/mod/lamslesson` and that Moodle globals (`$CFG`, `$DB`, `$USER`, etc.) come from `config.php`.

**Fast Verification**

- Quick smoke test: `php tests/lib_test_simple.php`
- `tests/lib_test_simple.php` hard-codes `/data/moodle/web52/public/config.php`; if the Moodle root path differs, update that require.
- Moodle PHPUnit test exists at `tests/lib_test.php` (`advanced_testcase`) and must be run via Moodle’s PHPUnit runner (it is not a standalone `php` script).

**Configuration Prereqs (Required For Any LAMS Call)**

- Admin settings (see `settings.php`) populate `$CFG->lamslesson_serverurl`, `$CFG->lamslesson_serverid`, `$CFG->lamslesson_serverkey`, `$CFG->lamslesson_servertimeoffset`.
- If these aren’t set, most LAMS entrypoints abort (`print_error(get_string('notsetup', 'lamslesson'))`) and LAMS callbacks return `HTTP 401`.

**Entrypoints / What To Read First**

- `lib.php`: main integration logic and module API functions (`lamslesson_add_instance`, `lamslesson_get_url`, `lamslesson_http_call_post`, etc.).
- `mod_form.php`: activity setup UI; fetches LAMS learning designs via `lamslesson_get_sequences_rest()` and stores the chosen `sequence_id`.
- `view.php`: activity runtime; generates learner/monitor URLs and pulls completion/grade updates from LAMS.
- `callback.php` and `userinfo.php`: called by LAMS; do not change parameter names lightly.

**LAMS Callback Auth (Do Not Break)**

- `callback.php` expects `hs`, `ts`, `un`, `lsId`.
- `userinfo.php` expects `hs`, `ts`, `un` (optional `lsid`).
- Both authenticate with `sha1(strtolower(trim(ts).trim(un).trim(serverId).trim(serverKey)))` and return `HTTP 401` on mismatch.

**Repo-Specific Gotcha**

- `tests/lib_test_simple.php` checks for the substring `declare(strict_types=1)` in `lib.php`. `lib.php` currently contains that substring in a comment (`//declare(strict_types=1);`). If you remove it during refactors, that test will start failing.
