<?php

require_once('BaseModel.php');

require_once(__ROOT__ . '/traits/JsonSerializable.php');

class EventModel extends BaseModel implements JsonSerializable
{
    use JsonSerializableTrait;
    public static string $nome_tabella = 'Prenotazioni';
    protected array $_fields = [
        "id",
        "titolo",
        "arrivo",
        "partenza",
        "capo_gruppo",
        "email",
        "telefono",
        "note",
        "numero_allegati",
    ];

}