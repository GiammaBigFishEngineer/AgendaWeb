<?php

require_once('BaseModel.php');

require_once(__ROOT__ . '/utils/Validation.php');

class PasswordResetModel extends BaseModel
{
    use JsonSerializableTrait;
    public static string $nome_tabella = 'PasswordReset';
    protected array $_fields = [
        "id",
        "id_user",
        "key",
        "approved",
        "requested_at",
    ];

    public static function generateKey($length = 63) {
        return bin2hex(random_bytes($length / 2));
    }

}
