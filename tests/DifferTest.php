<?php
use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    public function testGenDiffFilesNonExist()
    {
        $this->expectException(Error::class);
        genDiff('/file/not/exists1', '/file/not/exists2');
    }

    public function testGenDiffJson()
    {
        $file1Path = $this->getFixtureFilePath('file1.json');
        $file2Path = $this->getFixtureFilePath('file2.json');
        $diff = $this->getFixtureFileContent('json.diff');
        $this->assertEquals($diff, genDiff($file1Path, $file2Path));
    }

    public function testGenDiffYaml()
    {
        $file1Path = $this->getFixtureFilePath('file1.yml');
        $file2Path = $this->getFixtureFilePath('file2.yml');
        $diff = $this->getFixtureFileContent('json.diff');
        $this->assertEquals($diff, genDiff($file1Path, $file2Path));
    }

    public function testGenDiffComplexJson()
    {
        $file1Path = $this->getFixtureFilePath('file1complex.json');
        $file2Path = $this->getFixtureFilePath('file2complex.json');
        $diff = $this->getFixtureFileContent('complex.json.diff');
        $this->assertEquals($diff, genDiff($file1Path, $file2Path));
    }

    public function testGenDiffComplexYaml()
    {
        $file1Path = $this->getFixtureFilePath('file1complex.yaml');
        $file2Path = $this->getFixtureFilePath('file2complex.yaml');
        $diff = $this->getFixtureFileContent('complex.json.diff');
        $this->assertEquals($diff, genDiff($file1Path, $file2Path));
    }

    public function testGenDiffComplexJsonToPlainFormat()
    {
        $file1Path = $this->getFixtureFilePath('file1complex.json');
        $file2Path = $this->getFixtureFilePath('file2complex.json');
        $diff = $this->getFixtureFileContent('complex.plain.diff');
        $this->assertEquals($diff, genDiff($file1Path, $file2Path, 'plain'));
    }

    public function testGenDiffComplexYamlToPlainFormat()
    {
        $file1Path = $this->getFixtureFilePath('file1complex.yaml');
        $file2Path = $this->getFixtureFilePath('file2complex.yaml');
        $diff = $this->getFixtureFileContent('complex.plain.diff');
        $this->assertEquals($diff, genDiff($file1Path, $file2Path, 'plain'));
    }

    function getFixtureFilePath(string $fixtureName): string
    {
        return sprintf("%s/fixtures/%s", __DIR__, $fixtureName);
    }

    function getFixtureFileContent(string $fixtureName): string
    {
        return file_get_contents($this->getFixtureFilePath($fixtureName));
    }
}