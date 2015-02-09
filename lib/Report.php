<?php
namespace Combine;

class Report
{
    /**
     * @var Entry[]
     */
    protected $entries = array();

    /**
     * cutof Date
     * @var string
     */
    protected $cutoff;

    public function __construct($cutoff)
    {
        $this->cutoff = $cutoff;
    }

    /**
     * Builds a multi-dimentional array of entries
     *
     * @param Entry $entry
     */
    public function addEntry(Entry $entry)
    {
        $questionId = $entry->getQuestion()->getId();
        $department = $entry->getUser()->getDepartment();

        if (!isset($this->entries[$questionId])) {
            $this->entries[$questionId] = array();
        }

        if (!isset($this->entries[$questionId][$department])) {
            $this->entries[$questionId][$department] = array();
        }
        $this->entries[$questionId][$department][] = $entry;
    }
}