<?php

namespace IO\Tests\Feature\VDI\FMD;


use IO\Tests\Asserts\IsEqualArrayStructure;
use IO\Tests\TestCase;

class AttributeFMDTest extends TestCase
{
    protected function setUp()
    {
       parent::setUp();
    }

    /** @test */
    public function should_map_vdi_result_to_es_result()
    {
        $mappedVDIData = [
            'attribute' =>[
                'id' => 1,
                'name' => 'Bla',
                'shitField' => true
            ]
        ];


        $expectedStructure = [
            'attribute' => [
                'id' => '',
                'name' => '',
                'dsfsdf' => ''
            ]
        ];

        try
        {
            $this->assertTrue(IsEqualArrayStructure::validate($mappedVDIData, $expectedStructure));
        } catch(\Exception $exception)
        {
            $this->fail("Exception was not expected, but thrown anyway: Code " . $exception->getCode() . ", Message: " . $exception->getMessage());
        }
    }

}
