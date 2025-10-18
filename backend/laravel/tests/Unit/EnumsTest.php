<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\CropTypeEnum;

class EnumsTest extends TestCase
{
    /** @test */
    public function crop_type_enum_has_correct_values()
    {
        $this->assertEquals('cafe', CropTypeEnum::COFFEE->value);
        $this->assertEquals('Café', CropTypeEnum::COFFEE->label());

        $this->assertEquals('cana_de_acucar', CropTypeEnum::SUGAR_CANE->value);
        $this->assertEquals('Cana-de-açúcar', CropTypeEnum::SUGAR_CANE->label());
    }

    /** @test */
    public function crop_type_enum_labels_are_strings()
    {
        foreach (CropTypeEnum::cases() as $crop) {
            $this->assertIsString($crop->label());
            $this->assertNotEmpty($crop->label());
        }
    }

    /** @test */
    public function crop_type_enum_values_are_strings()
    {
        foreach (CropTypeEnum::cases() as $crop) {
            $this->assertIsString($crop->value);
            $this->assertNotEmpty($crop->value);
        }
    }

    /** @test */
    public function crop_type_enum_has_coffee_and_sugar_cane()
    {
        $enumValues = array_map(fn($case) => $case->value, CropTypeEnum::cases());

        $this->assertContains('cafe', $enumValues);
        $this->assertContains('cana_de_acucar', $enumValues);
    }
}
