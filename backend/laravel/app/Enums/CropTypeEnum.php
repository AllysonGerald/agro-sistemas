<?php

namespace App\Enums;

enum CropTypeEnum: string
{
    // Frutas Tropicais e Cítricas
    case ORANGE_PERA = 'laranja_pera';
    case ORANGE_LIMA = 'laranja_lima';
    case LEMON = 'limao';
    case CASHEW = 'caju';
    case MANGO = 'manga';
    case COCONUT = 'coco';
    case PAPAYA = 'mamao';
    case BANANA = 'banana';
    case PINEAPPLE = 'abacaxi';
    case PASSION_FRUIT = 'maracuja';
    case ACEROLA = 'acerola';
    case SOURSOP = 'graviola';
    case JACKFRUIT = 'jaca';
    case AVOCADO = 'abacate';

    // Frutas Regionais do Nordeste
    case WATERMELON_CRIMSON_SWEET = 'melancia_crimson_sweet';
    case WATERMELON = 'melancia';
    case MELON = 'melao';
    case GUAVA_PALUMA = 'goiaba_paluma';
    case GUAVA = 'goiaba';
    case SIRIGUELA = 'siriguela';
    case PITANGA = 'pitanga';
    case UMBU = 'umbu';
    case CAJA = 'caja';

    // Grãos e Cereais
    case CORN = 'milho';
    case BEAN_COWPEA = 'feijao_caupi';
    case BEAN_COMMON = 'feijao_comum';
    case SOYBEAN = 'soja';
    case RICE = 'arroz';
    case WHEAT = 'trigo';
    case SORGHUM = 'sorgo';
    case SUNFLOWER = 'girassol';
    case SESAME = 'gergelim';

    // Culturas Industriais
    case SUGAR_CANE = 'cana_de_acucar';
    case COFFEE = 'cafe';

    // Tubérculos e Raízes
    case CASSAVA = 'mandioca';
    case SWEET_POTATO = 'batata_doce';
    case YAM = 'inhame';
    case POTATO = 'batata_inglesa';

    // Hortaliças e Verduras
    case TOMATO = 'tomate';
    case ONION = 'cebola';
    case CARROT = 'cenoura';
    case LETTUCE = 'alface';
    case CABBAGE = 'repolho';
    case BELL_PEPPER = 'pimentao';
    case CUCUMBER = 'pepino';
    case OKRA = 'quiabo';
    case EGGPLANT = 'berinjela';
    case PUMPKIN = 'abobora';

    // Plantas Forrageiras
    case BRACHIARIA = 'capim_braquiaria';
    case TANZANIA_GRASS = 'capim_tanzania';
    case ELEPHANT_GRASS = 'capim_elefante';
    case ALFALFA = 'alfafa';
    case FORAGE_PALM = 'palma_forrageira';
    case LEUCENA = 'leucena';

    // Algodão e Fibras
    case COTTON = 'algodao';
    case SISAL = 'sisal';

    // Plantas Medicinais e Aromáticas
    case MINT = 'hortela';
    case BASIL = 'manjericao';
    case LEMONGRASS = 'capim_santo';
    case CHAMOMILE = 'camomila';

    public function label(): string
    {
        return match($this) {
            // Frutas Tropicais e Cítricas
            self::ORANGE_PERA => 'Laranja Pera',
            self::ORANGE_LIMA => 'Laranja Lima',
            self::LEMON => 'Limão',
            self::CASHEW => 'Caju',
            self::MANGO => 'Manga',
            self::COCONUT => 'Coco',
            self::PAPAYA => 'Mamão',
            self::BANANA => 'Banana',
            self::PINEAPPLE => 'Abacaxi',
            self::PASSION_FRUIT => 'Maracujá',
            self::ACEROLA => 'Acerola',
            self::SOURSOP => 'Graviola',
            self::JACKFRUIT => 'Jaca',
            self::AVOCADO => 'Abacate',

            // Frutas Regionais do Nordeste
            self::WATERMELON_CRIMSON_SWEET => 'Melancia Crimson Sweet',
            self::WATERMELON => 'Melancia',
            self::MELON => 'Melão',
            self::GUAVA_PALUMA => 'Goiaba Paluma',
            self::GUAVA => 'Goiaba',
            self::SIRIGUELA => 'Siriguela',
            self::PITANGA => 'Pitanga',
            self::UMBU => 'Umbu',
            self::CAJA => 'Cajá',

            // Grãos e Cereais
            self::CORN => 'Milho',
            self::BEAN_COWPEA => 'Feijão Caupi',
            self::BEAN_COMMON => 'Feijão Comum',
            self::SOYBEAN => 'Soja',
            self::RICE => 'Arroz',
            self::WHEAT => 'Trigo',
            self::SORGHUM => 'Sorgo',
            self::SUNFLOWER => 'Girassol',
            self::SESAME => 'Gergelim',

            // Culturas Industriais
            self::SUGAR_CANE => 'Cana-de-açúcar',
            self::COFFEE => 'Café',

            // Tubérculos e Raízes
            self::CASSAVA => 'Mandioca',
            self::SWEET_POTATO => 'Batata Doce',
            self::YAM => 'Inhame',
            self::POTATO => 'Batata Inglesa',

            // Hortaliças e Verduras
            self::TOMATO => 'Tomate',
            self::ONION => 'Cebola',
            self::CARROT => 'Cenoura',
            self::LETTUCE => 'Alface',
            self::CABBAGE => 'Repolho',
            self::BELL_PEPPER => 'Pimentão',
            self::CUCUMBER => 'Pepino',
            self::OKRA => 'Quiabo',
            self::EGGPLANT => 'Berinjela',
            self::PUMPKIN => 'Abóbora',

            // Plantas Forrageiras
            self::BRACHIARIA => 'Capim Braquiária',
            self::TANZANIA_GRASS => 'Capim Tanzânia',
            self::ELEPHANT_GRASS => 'Capim Elefante',
            self::ALFALFA => 'Alfafa',
            self::FORAGE_PALM => 'Palma Forrageira',
            self::LEUCENA => 'Leucena',

            // Algodão e Fibras
            self::COTTON => 'Algodão',
            self::SISAL => 'Sisal',

            // Plantas Medicinais e Aromáticas
            self::MINT => 'Hortelã',
            self::BASIL => 'Manjericão',
            self::LEMONGRASS => 'Capim Santo',
            self::CHAMOMILE => 'Camomila',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn($case) => [
                'value' => $case->value,
                'label' => $case->label()
            ],
            self::cases()
        );
    }
}
