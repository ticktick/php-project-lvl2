<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;
use Error;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    public function testGenDiffFilesNonExist()
    {
        $this->expectException(Error::class);
        genDiff('/file/not/exists1', '/file/not/exists2');
    }

    /**
     * @dataProvider additionProvider
     */
    public function testGenDiff($fileName1, $fileName2, $diffFileName, $format = 'pretty')
    {
        $file1Path = $this->getFixtureFilePath($fileName1);
        $file2Path = $this->getFixtureFilePath($fileName2);
        $diff = $this->getFixtureFileContent($diffFileName);
        $this->assertEquals($diff, genDiff($file1Path, $file2Path, $format));
    }

    public function additionProvider()
    {
        return [
            'flat json files' =>
                ['file1.json', 'file2.json', 'pretty.diff'],
            'flat yaml files' =>
                ['file1.yml', 'file2.yml', 'pretty.diff'],
            'complex struct json files, default format' =>
                ['file1complex.json', 'file2complex.json', 'complex.pretty.diff'],
            'complex struct yaml files, pretty format' =>
                ['file1complex.yaml', 'file2complex.yaml', 'complex.pretty.diff', 'pretty'],
            'complex struct json files, plain format' =>
                ['file1complex.json', 'file2complex.json', 'complex.plain.diff', 'plain'],
            'complex struct yaml files, plain format' =>
                ['file1complex.yaml', 'file2complex.yaml', 'complex.plain.diff', 'plain'],
            'complex struct json files, json format' =>
                ['file1complex.json', 'file2complex.json', 'complex.json.diff', 'json'],
            'complex struct yaml files, json format' =>
                ['file1complex.yaml', 'file2complex.yaml', 'complex.json.diff', 'json'],
        ];
    }

    public function getFixtureFilePath(string $fixtureName): string
    {
        return sprintf("%s/fixtures/%s", __DIR__, $fixtureName);
    }

    public function getFixtureFileContent(string $fixtureName): string
    {
        return file_get_contents($this->getFixtureFilePath($fixtureName));
    }
}
