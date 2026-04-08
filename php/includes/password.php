<?php
/**
 * WordPress-compatible password hashing and verification.
 *
 * Implements the phpass portable hash scheme used by WordPress ($P$ prefix).
 * Also handles:
 *   - bcrypt ($2y$) — via PHP's native password_verify()
 *   - $wp$ prefix — bcrypt via wp-passwords-bcrypt plugin (strips prefix, then bcrypt)
 *   - Legacy MD5 — 32-char hex strings
 */

const CG_ITOA64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

function cg_phpass_encode64(string $input, int $count): string {
    $output = '';
    $i = 0;
    do {
        $value   = ord($input[$i++]);
        $output .= CG_ITOA64[$value & 0x3f];
        if ($i < $count) $value |= ord($input[$i]) << 8;
        $output .= CG_ITOA64[($value >> 6) & 0x3f];
        if ($i++ >= $count) break;
        if ($i < $count) $value |= ord($input[$i]) << 16;
        $output .= CG_ITOA64[($value >> 12) & 0x3f];
        if ($i++ >= $count) break;
        $output .= CG_ITOA64[($value >> 18) & 0x3f];
    } while ($i < $count);
    return $output;
}

function cg_phpass_crypt(string $password, string $setting): string {
    $output     = '*0';
    if (substr($setting, 0, 2) === $output) $output = '*1';

    $id = substr($setting, 0, 3);
    if ($id !== '$P$' && $id !== '$H$') return $output;

    $count_log2 = strpos(CG_ITOA64, $setting[3]);
    if ($count_log2 < 7 || $count_log2 > 30) return $output;

    $count = 1 << $count_log2;
    $salt  = substr($setting, 4, 8);
    if (strlen($salt) !== 8) return $output;

    $hash = md5($salt . $password, true);
    do { $hash = md5($hash . $password, true); } while (--$count);

    return substr($setting, 0, 12) . cg_phpass_encode64($hash, 16);
}

/**
 * Verify a plaintext password against a WordPress password hash.
 */
function cg_check_password(string $password, string $hash): bool {
    // $wp$ prefix (wp-passwords-bcrypt plugin)
    // Format: $wp$$2y$... — strip the 4-char "$wp$" prefix to get the real bcrypt hash
    if (str_starts_with($hash, '$wp$')) {
        // Log the raw hash structure (first 16 chars only, safe to log) for diagnosis
        $rawSample = substr($hash, 0, 16);
        error_log("[CG pw] \$wp\$ raw sample='" . $rawSample . "' len=" . strlen($hash));
        // Try both formats:
        //   Format A: $wp$$2y$... (plugin prepends "$wp$" before real bcrypt "$2y$...")
        //   Format B: $wp$2y$...  (plugin stores without the leading "$" of bcrypt)
        $suffix = substr($hash, 4);
        if (str_starts_with($suffix, '$')) {
            // Format A: suffix already starts with "$" → real bcrypt hash = suffix
            $real = $suffix;
        } else {
            // Format B: suffix missing the leading "$" → restore it
            $real = '$' . $suffix;
        }
        error_log("[CG pw] reconstructed real='" . substr($real, 0, 16) . "'");
        return password_verify($password, $real);
    }

    // bcrypt (WordPress 6.8+ core or bcrypt plugin)
    if (str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$') || str_starts_with($hash, '$2b$')) {
        return password_verify($password, $hash);
    }

    // phpass portable hash
    if (str_starts_with($hash, '$P$') || str_starts_with($hash, '$H$')) {
        return cg_phpass_crypt($password, $hash) === $hash;
    }

    // Legacy MD5
    if (strlen($hash) === 32 && ctype_xdigit($hash)) {
        return md5($password) === $hash;
    }

    return false;
}

/**
 * Hash a password using phpass (WordPress-compatible, $P$ format).
 */
function cg_hash_password(string $password): string {
    $count_log2 = 8;
    $random     = '';
    for ($i = 0; $i < 6; $i++) $random .= chr(random_int(0, 255));
    $setting = '$P$' . CG_ITOA64[$count_log2] . cg_phpass_encode64($random, 6);
    return cg_phpass_crypt($password, $setting);
}
