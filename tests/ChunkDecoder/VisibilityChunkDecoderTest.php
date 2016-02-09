<?php

namespace MetarDecoder\Test\ChunkDecoder;

use MetarDecoder\ChunkDecoder\VisibilityChunkDecoder;

class VisibilityChunkDecoderTest extends \PHPUnit_Framework_TestCase
{
    private $decoder;

    protected function setup()
    {
        $this->decoder = new VisibilityChunkDecoder();
    }

    /**
     * Test parsing of valid surface wind chunks
     * @param $chunk
     * @param $cavok
     * @param $visibility
     * @param $visibility_unit
     * @param $minimum
     * @param $minimum_direction
     * @param $remaining
     * @dataProvider getChunk
     */
    public function testParse($chunk, $cavok, $visibility, $visibility_m, $visibility_SM,
                              $visibility_unit, $minimum, $minimum_direction, $remaining)
    {
        $decoded = $this->decoder->parse($chunk);
        if ($cavok) {
            $this->assertTrue($decoded['result']['cavok']);
        } elseif ($visibility == null) {
            $this->assertNull($decoded['result']['visibility']);
            $this->assertFalse($decoded['result']['cavok']);
        } else {
            $vis = $decoded['result']['visibility'];
            $this->assertEquals($visibility, $vis->getVisibility()->getValue());
            $this->assertEquals($visibility_m, $vis->getVisibility()->getConvertedValue('m'));
            $this->assertEquals($visibility_SM, $vis->getVisibility()->getConvertedValue('SM'));
            $this->assertEquals($visibility_unit, $vis->getVisibility()->getUnit());
            if ($minimum != null) {
                $this->assertEquals($minimum, $vis->getMinimumVisibility()->getValue());
                $this->assertEquals($minimum_direction, $vis->getMinimumVisibilityDirection());
            }
        }
        $this->assertEquals($remaining, $decoded['remaining_metar']);
    }

    /**
     * Test "Conversion rate not defined" Exception during unit conversion
     * @param $chunk
     * @dataProvider getChunk
     */
    public function testParseConverterException($chunk, $cavok, $visibility){

        $decoded = $this->decoder->parse($chunk);
        if(isset($visibility)){
            $vis = $decoded['result']['visibility'];
            $this->setExpectedException('Exception', 'Conversion rate between "m" and "aaa" is not defined.');
            $this->assertEquals($visibility, $vis->getVisibility()->getConvertedValue('aaa'));
        }

    }

    /**
     * Test parsing of invalid surface wind chunks
     * @param $chunk
     * @expectedException \MetarDecoder\Exception\ChunkDecoderException
     * @dataProvider getInvalidChunk
     */
    public function testParseInvalidChunk($chunk)
    {
        $this->decoder->parse($chunk);
    }

    public function getChunk()
    {
        return array(
            array(
                "chunk" => "0200 AAA",
                "cavok" => false,
                "visibility" => 200,
                "visibility_m" => 200,
                "visibility_SM" => 0.124,
                "visibility_unit" => 'm',
                "minimum" => null,
                "minimum_direction" => null,
                "remaining" => "AAA",
            ),
            array(
                "chunk" => "CAVOK BBB",
                "cavok" => true,
                "visibility" => null,
                "visibility_m" => null,
                "visibility_SM" => null,
                "visibility_unit" => 'm',
                "minimum" => null,
                "minimum_direction" => null,
                "remaining" => "BBB",
            ),
            array(
                "chunk" => "8000 1200N CCC",
                "cavok" => false,
                "visibility" => 8000,
                "visibility_m" => 8000,
                "visibility_SM" => 4.971,
                "visibility_unit" => 'm',
                "minimum" => 1200,
                "minimum_direction" => "N",
                "remaining" => "CCC",
            ),
            array(
                "chunk" => "2500 2200 DDD",
                "cavok" => false,
                "visibility" => 2500,
                "visibility_m" => 2500,
                "visibility_SM" => 1.553,
                "visibility_unit" => 'm',
                "minimum" => 2200,
                "minimum_direction" => null,
                "remaining" => "DDD",
            ),
            array(
                "chunk" => "1 1/4SM EEE",
                "cavok" => false,
                "visibility" => 1.25,
                "visibility_m" => 2011.675,
                "visibility_SM" => 1.25,
                "visibility_unit" => 'SM',
                "minimum" => null,
                "minimum_direction" => null,
                "remaining" => "EEE",
            ),
            array(
                "chunk" => "10SM FFF",
                "cavok" => false,
                "visibility" => 10,
                "visibility_m" => 16093.400,
                "visibility_SM" => 10,
                "visibility_unit" => 'SM',
                "minimum" => null,
                "minimum_direction" => null,
                "remaining" => "FFF",
            ),
            array(
                "chunk" => "3/4SM GGG",
                "cavok" => false,
                "visibility" => 0.75,
                "visibility_m" => 1207.005,
                "visibility_SM" => 0.75,
                "visibility_unit" => 'SM',
                "minimum" => null,
                "minimum_direction" => null,
                "remaining" => "GGG",
            ),
            array(
                "chunk" => "//// HHH",
                "cavok" => false,
                "visibility" => null,
                "visibility_m" => null,
                "visibility_SM" => null,
                "visibility_unit" => null,
                "minimum" => null,
                "minimum_direction" => null,
                "remaining" => "HHH",
            ),
        );
    }

    public function getInvalidChunk()
    {
        return array(
            array("chunk" => "CAVO EEE"),
            array("chunk" => "CAVOKO EEE"),
            array("chunk" => "123 EEE"),
            array("chunk" => "12335 EEE"),
        );
    }
}
