<?php

require_once('BaseModel.php');

require_once(__ROOT__ . '/controllers/FileController.php');

require_once(__ROOT__ . '/traits/JsonSerializable.php');

require_once(__ROOT__ . '/utils/Validation.php');

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
        "stato",
        "colore",
        "termine_saldo",
        "caparre",
        "totale"
    ];

    protected array $calculated_fields = [
        "caparre",
        "saldo"
    ];

    public static function delete(int $id): void
    {
        $event = self::get($id);

        // Delete documents
        $fileController = new FileController($event);
        $fileController->deleteParentFolder();

        parent::delete($id);
    }

    public static function getByDates($start, $end){
        $query = "SELECT * FROM " . static::$nome_tabella  . ` WHERE partenza BETWEEN {$start} AND {$end};`;
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

    public function getCaparre(){
        $model = $this->getData();

        if(!isset($model["caparre"]) || $model["caparre"] === null) {
            return "[]";
        } else {
            return $model["caparre"];
        }
    }

    public function getSaldo(){
        $model = $this->getData();
        
        if( isset($model["totale"]) && isset($model["caparre"]) ) {
            $caparre = json_decode($model["caparre"]);

            $tot_cap = 0;
            foreach ($caparre as $key => $caparra) {
                $tot_cap += (float)$caparra->value;
            }

            $val = (float)$model["totale"] - $tot_cap;

            if($val < 0){
                return 0;
            }
            return $val;
        }

        return null;
    }

    public function validate(): bool
    {
        $this->totale = str_replace(',', '.', $this->totale);
        $this->totale = number_format($this->totale, 2, '.', '');

        return true;
    }
}

enum EventColor: int {
    case ROSSO = 0;
    case VERDE = 1;
    case AZZURRO = 2;
    case GIALLO = 3;
    case ARANCIONE = 4;
    case VIOLA = 5;

    public function toHex(int $darkness = 0): string {
        switch ($this) {
            case self::ROSSO:
                return self::darkenHexColor("#FF0000", $darkness);
            case self::VERDE:
                return self::darkenHexColor("#00FF00", $darkness);
            case self::AZZURRO:
                return self::darkenHexColor("#4B87FF", $darkness);
            case self::GIALLO:
                return self::darkenHexColor("#FFFF00", $darkness);
            case self::ARANCIONE:
                return self::darkenHexColor("#FFA500", $darkness);
            case self::VIOLA:
                return self::darkenHexColor("#8B00FF", $darkness);
            default:
                return "";
        }
    }

    public static function convert(int $val): self {
            return match ($val) {
            0 => EventColor::ROSSO,
            1 => EventColor::VERDE,
            2 => EventColor::AZZURRO,
            3 => EventColor::GIALLO,
            4 => EventColor::ARANCIONE,
            5 => EventColor::VIOLA,
            default => throw new Exception('Unknown color value')
        };
    }

    private function darkenHexColor($hexColor, $percent) {
        // Remove the leading # if present
        $hexColor = ltrim($hexColor, '#');
    
        // Convert the hex color to RGB
        list($r, $g, $b) = sscanf($hexColor, "%02x%02x%02x");
    
        // Darken the color by reducing the RGB values
        $r = max(0, $r - round($r * $percent / 100));
        $g = max(0, $g - round($g * $percent / 100));
        $b = max(0, $b - round($b * $percent / 100));
    
        // Convert the modified RGB values back to a hex color
        $darkenedHexColor = sprintf("#%02x%02x%02x", $r, $g, $b);
    
        return $darkenedHexColor;
    }


}