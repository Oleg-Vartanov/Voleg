<?php

namespace App\Core\Test\Unit;

use App\Core\Service\CsvReader;
use Exception;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('CsvReader')]
class CsvReaderTest extends TestCase
{
    private string $testFile;

    /** @inheritdoc */
    protected function setUp(): void
    {
        parent::setUp();
        $this->testFile = sys_get_temp_dir() . '/csv_reader_test.csv';
    }

    /** @inheritdoc */
    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }

    /**
     * @throws Exception
     */
    #[TestDox('Write & read: success')]
    public function testWriteAndReadCsv(): void
    {
        $csvReader = new CsvReader();
        $rows = [
            ['name', 'age', 'email'],
            ['Alice', '30', 'alice@example.com'],
            ['Bob', '25', 'bob@example.com'],
        ];

        $csvReader->write($this->testFile, $rows);
        self::assertFileExists($this->testFile);

        $readData = $csvReader->read($this->testFile);
        self::assertSame($rows, $readData);
    }

    #[TestDox('Read: invalid file exception')]
    public function testReadInvalidFileException(): void
    {
        $csvReader = new CsvReader();

        self::expectException(Exception::class);
        self::expectExceptionMessage('Unable to read file.');

        @ $csvReader->read('/path/to/nonexistent/file.csv');
    }

    #[TestDox('Write: invalid file exception')]
    public function testWriteInvalidFileException(): void
    {
        $csvReader = new CsvReader();

        // Attempt to write to a directory path instead of a file
        $invalidFilePath = sys_get_temp_dir();

        self::expectException(Exception::class);
        self::expectExceptionMessage("Unable to open file for writing: " . $invalidFilePath);

        @ $csvReader->write($invalidFilePath, [['test']]);
    }

    #[TestDox('Write: unable to write row exception')]
    public function testWriteUnableToWriteRowException(): void
    {
        $csvReader = self::createPartialMock(CsvReader::class, ['writeRow']);
        $csvReader->expects(self::once())->method('writeRow')->willReturn(false);

        self::expectException(Exception::class);
        self::expectExceptionMessage("Unable to write data to CSV file: " . $this->testFile);

        $csvReader->write($this->testFile, [['test']]);
    }
}
