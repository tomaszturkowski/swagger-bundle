<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Dev\Tests\Request\Transformer;

use KleijnWeb\SwaggerBundle\Request\Transformer\ParameterCoercer;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ParameterCoercerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider primitiveConversionProvider
     * @test
     *
     * @param string $type
     * @param mixed  $value
     * @param mixed  $expected
     * @param string $collectionFormat
     */
    public function willInterpretPrimitivesAsExpected($type, $value, $expected, $collectionFormat = 'csv')
    {
        $spec = ['type' => $type, 'name' => $value];
        if ($type === 'array') {
            $spec['collectionFormat'] = $collectionFormat;
        }

        $actual = ParameterCoercer::coerceParameter($spec, $value);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider failingPrimitiveConversionProvider
     * @test
     *
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\MalformedContentException
     *
     * @param string $type
     * @param mixed  $value
     */
    public function willFailToInterpretPrimitivesAsExpected($type, $value)
    {
        ParameterCoercer::coerceParameter(['type' => $type, 'name' => $value], $value);
    }

    /**
     * @dataProvider unsupportedPrimitiveConversionProvider
     * @test
     *
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\UnsupportedException
     *
     * @param array $spec
     * @param mixed $value
     */
    public function willThrowUnsupportedExceptionInPredefinedCases($spec, $value)
    {
        $spec = array_merge(['type' => 'string', 'name' => $value], $spec);
        ParameterCoercer::coerceParameter($spec, $value);
    }

    /**
     * @return array
     */
    public static function primitiveConversionProvider()
    {
        $now = new \DateTime();
        $midnight = new \DateTime('midnight today');

        return [
            ['boolean', '0', false],
            ['boolean', 'FALSE', false],
            ['boolean', 'false', false],
            ['boolean', '1', true],
            ['boolean', 'TRUE', true],
            ['boolean', 'true', true],
            ['integer', '1', 1],
            ['integer', '21474836470', 21474836470],
            ['integer', '00005', 5],
            ['number', '1', 1.0],
            ['number', '1.5', 1.5],
            ['number', '1', 1.0],
            ['number', '1.5', 1.5],
            ['string', '1', '1'],
            ['string', '1.5', '1.5'],
            ['string', '€', '€'],
            ['null', '', null],
            ['date', $midnight->format('Y-m-d'), $midnight],
            ['date-time', $now->format(\DateTime::W3C), $now],
            ['array', [1, 2, 3, 4], [1, 2, 3, 4]],
            ['array', 'a', ['a']],
            ['array', 'a,b,c', ['a', 'b', 'c']],
            ['array', 'a, b,c ', ['a', ' b', 'c ']],
            ['array', 'a', ['a'], 'ssv'],
            ['array', 'a b c', ['a', 'b', 'c'], 'ssv'],
            ['array', 'a  b c ', ['a', '', 'b', 'c', ''], 'ssv'],
            ['array', 'a', ['a'], 'tsv'],
            ['array', "a\tb\tc", ['a', 'b', 'c'], 'tsv'],
            ['array', "a\t b\tc ", ['a', ' b', 'c '], 'tsv'],
            ['array', 'a', ['a'], 'pipes'],
            ['array', 'a|b|c', ['a', 'b', 'c'], 'pipes'],
            ['array', 'a| b|c ', ['a', ' b', 'c '], 'pipes']
        ];
    }

    /**
     * @return array
     */
    public static function failingPrimitiveConversionProvider()
    {
        return [
            ['boolean', 'a'],
            ['boolean', ''],
            ['boolean', "\0"],
            ['boolean', null],
            ['integer', '1.0'],
            ['integer', 'TRUE'],
            ['integer', ''],
            ['number', 'b'],
            ['number', 'FALSE'],
            ['null', '0'],
            ['null', 'FALSE']
        ];
    }

    /**
     * @return array
     */
    public static function unsupportedPrimitiveConversionProvider()
    {
        return [
            [['type' => 'array', 'collectionFormat' => 'multi'], ''],
            [['type' => 'array', 'collectionFormat' => 'foo'], ''],
        ];
    }
}