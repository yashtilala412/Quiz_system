<?php
/**
 * Main class for spreadsheet reading
 *
 * @version 0.5.10
 * @author Martins Pilsetnieks
 */
class SpreadsheetReader implements SeekableIterator, Countable
{
    // Constants
    const TYPE_XLSX = 'XLSX';
    const TYPE_CSV = 'CSV';

    // Properties
    private $Filepath;
    private $Type;
    private $Options = [];
    private $Handle;
    private $Sheets = [];
    private $CurrentSheetIndex = 0;

    // Constructor
    public function __construct($Filepath, $Type, $Options = [])
    {
        $this -> Filepath = $Filepath;
        $this -> Type = $Type;
        $this -> Options = $Options;
        $this -> openFile();
    }

    // Method to open the file
    private function openFile()
    {
        switch ($this -> Type)
        {
            case self::TYPE_XLSX:
                $this -> Handle = new XLSXReader($this -> Filepath, $this -> Options);
                break;
            case self::TYPE_CSV:
                $this -> Handle = new CSVReader($this -> Filepath, $this -> Options);
                break;
            default:
                throw new Exception('SpreadsheetReader: Unsupported file type');
        }

        $this -> Sheets = $this -> Handle -> getSheets();
    }

    // Method to close the file
    public function close()
    {
        $this -> Handle -> close();
    }

    // SeekableIterator methods
    public function current()
    {
        return $this -> Handle -> current();
    }

    public function key()
    {
        return $this -> Handle -> key();
    }

    public function next()
    {
        $this -> Handle -> next();
    }

    public function rewind()
    {
        $this -> Handle -> rewind();
    }

    public function seek($Position)
    {
        $this -> Handle -> seek($Position);
    }

    public function valid()
    {
        return $this -> Handle -> valid();
    }

    // Countable method
    public function count()
    {
        return $this -> Handle -> count();
    }

    // Additional methods
    public function switchSheet($SheetIndex)
    {
        if (isset($this -> Sheets[$SheetIndex]))
        {
            $this -> CurrentSheetIndex = $SheetIndex;
            $this -> Handle -> switchSheet($SheetIndex);
        }
        else
        {
            throw new Exception('SpreadsheetReader: Invalid sheet index');
        }
    }

    public function setDelimiter($Delimiter)
    {
        if ($this -> Type == self::TYPE_CSV)
        {
            $this -> Options['Delimiter'] = $Delimiter;
            $this -> Handle -> setDelimiter($Delimiter);
        }
        else
        {
            throw new Exception('SpreadsheetReader: Delimiter can only be set for CSV files');
        }
    }

    public function setEnclosure($Enclosure)
    {
        if ($this -> Type == self::TYPE_CSV)
        {
            $this -> Options['Enclosure'] = $Enclosure;
            $this -> Handle -> setEnclosure($Enclosure);
        }
        else
        {
            throw new Exception('SpreadsheetReader: Enclosure can only be set for CSV files');
        }
    }

    public function getCurrentSheetName()
    {
        return $this -> Handle -> getCurrentSheetName();
    }

    public function getTotalSheetsCount()
    {
        return count($this -> Sheets);
    }

    public function isEmpty()
    {
        return $this -> count() == 0;
    }

    public function setDateFormat($DateFormat)
    {
        if ($this -> Handle)
        {
            $this -> Handle -> setDateFormat($DateFormat);
        }
    }

    public function getMetadata()
    {
        return $this -> Handle -> getMetadata();
    }

    public function getCell($Row, $Column)
    {
        return $this -> Handle -> getCell($Row, $Column);
    }

    public function setCell($Row, $Column, $Value)
    {
        $this -> Handle -> setCell($Row, $Column, $Value);
    }

    public function addValidationRule(callable $Rule)
    {
        $this -> Handle -> addValidationRule($Rule);
    }

    public function validate()
    {
        return $this -> Handle -> validate();
    }

    public function getRow($Row)
    {
        return $this -> Handle -> getRow($Row);
    }

    public function getColumn($Column)
    {
        return $this -> Handle -> getColumn($Column);
    }

    public function toArray()
    {
        return $this -> Handle -> toArray();
    }

    public function save($Filepath)
    {
        $this -> Handle -> save($Filepath);
    }

    public function addRow(array $Row)
    {
        $this -> Handle -> addRow($Row);
    }

    public function deleteRow($Row)
    {
        $this -> Handle -> deleteRow($Row);
    }

    public function addColumn(array $Column)
    {
        $this -> Handle -> addColumn($Column);
    }

    public function deleteColumn($Column)
    {
        $this -> Handle -> deleteColumn($Column);
    }

    public function findCellValue($Value)
    {
        return $this -> Handle -> findCellValue($Value);
    }

    public function setCellBackgroundColor($Row, $Column, $Color)
    {
        $this -> Handle -> setCellBackgroundColor($Row, $Column, $Color);
    }

    public function setCellFontStyle($Row, $Column, $FontStyle)
    {
        $this -> Handle -> setCellFontStyle($Row, $Column, $FontStyle);
    }

    public function mergeCells($StartRow, $StartColumn, $EndRow, $EndColumn)
    {
        $this -> Handle -> mergeCells($StartRow, $StartColumn, $EndRow, $EndColumn);
    }

    public function splitMergedCells($Row, $Column)
    {
        $this -> Handle -> splitMergedCells($Row, $Column);
    }

    public function freezePanes($Row, $Column)
    {
        $this -> Handle -> freezePanes($Row, $Column);
    }

    public function unfreezePanes()
    {
        $this -> Handle -> unfreezePanes();
    }

    public function protectSheet($Password)
    {
        $this -> Handle -> protectSheet($Password);
    }

    public function unprotectSheet()
    {
        $this -> Handle -> unprotectSheet();
    }

    public function setSheetVisibility($Visible)
    {
        $this -> Handle -> setSheetVisibility($Visible);
    }
}

?>