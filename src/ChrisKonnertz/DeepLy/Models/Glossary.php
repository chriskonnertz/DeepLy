<?php

namespace ChrisKonnertz\DeepLy\Models;

use DateTime;

class Glossary extends Model
{

    const PROPERTY_MAPPINGS = ['source_lang' => 'from', 'target_lang' => 'to', 'creation_time' => 'creationTimeIso'];

    /**
     * The unique identifier of the glossary
     * @var string
     */
    public string $glossaryId = '';

    /**
     * The name of the glossary
     * @var string
     */
    public string $name = '';

    /**
     * The state of the glossary
     * @var bool
     */
    public bool $ready = false;

    /**
     * The source language
     * @var string
     */
    public string $from = '';

    /**
     * The target language
     * @var string
     */
    public string $to = '';

    /**
     * The creation time of the glossary, as a ISO 8601 string
     * @var string
     */
    public string $creationTimeIso = '';

    /**
     * The creation time of the glossary, as a DateTime object
     * @var DateTime|null
     */
    public DateTime|null $creationDateTime= null;

    /**
     * The number of entries in the glossary
     * @var int
     */
    public int $entryCount = 0;

    /**
     * @inheritDoc
     */
    protected function enrich()
    {
        if ($this->creationTimeIso) {
            $this->creationDateTime = new DateTime($this->creationTimeIso);
        }
    }


}