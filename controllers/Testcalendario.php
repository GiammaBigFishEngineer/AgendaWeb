<?php

class TestCalendario{
    public static function test() {
        $annoCorrente = date('Y');
        $mesiNomi = array(
            1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo', 4 => 'Aprile', 5 => 'Maggio', 6 => 'Giugno',
            7 => 'Luglio', 8 => 'Agosto', 9 => 'Settembre', 10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre'
        );
        $calendario = array();
        for ($mese = 1; $mese <= 12; $mese++) {
                    
            $giorniDelMese = cal_days_in_month(CAL_GREGORIAN, $mese, $annoCorrente);
            $meseNome = $mesiNomi[$mese];
            
            $calendario[$meseNome] = array();
            
            for ($giorno = 1; $giorno <= $giorniDelMese; $giorno++) {
                $calendario[$meseNome][] = $giorno;
            }
        }
    }
}