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

    function getFixtureFilePath(string $fixtureName): string
    {
        return sprintf("%s/fixtures/%s", __DIR__, $fixtureName);
    }

    function getFixtureFileContent(string $fixtureName): string
    {
        return file_get_contents($this->getFixtureFilePath($fixtureName));
    }
}