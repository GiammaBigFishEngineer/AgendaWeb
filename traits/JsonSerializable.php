<?php

trait JsonSerializableTrait {
    public function jsonSerialize(): mixed
    {
        $obj_vars = $this->getData();

        if ($obj_vars != null) {
            $data = isset($this->hidden_fields) ? array_diff_key($obj_vars, array_flip($this->hidden_fields)) : $obj_vars; 
            return $data;
        }

        return null;
    }

}