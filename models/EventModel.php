<?php

require_once('BaseModel.php');

class EventModel extends BaseModel
{
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