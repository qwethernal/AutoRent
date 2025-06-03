<?php


function get_localized_description($car_data, $lang) {
    $desc_field = "description_{$lang}";


    if (!empty($car_data[$desc_field])) {
        return $car_data[$desc_field];
    }


    return $car_data['description_ru'] ?? $car_data['description'] ?? 'Описание недоступно';
}


?>