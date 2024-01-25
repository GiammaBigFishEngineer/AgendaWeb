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

    public static function getByDates($start, $end){
        $query = "SELECT * FROM " . static::$nome_tabella  . ` WHERE partenza BETWEEN {$start} AND {$end}`;
        $sth = DB::get()->prepare($query);
        $sth->execute();

        $list = $sth->fetchAll();

        $entities = array();
        foreach ($list as $row) {
            $entities[] = new static($row);
        }

        return $entities;
        // return new static(($row == false) ? [] : $row);
    }
}