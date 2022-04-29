<?php

namespace ChrisKonnertz\DeepLy\Models;

class DocumentState extends Model
{
    
    /**
     * Possible status values
     */
    const STATUS_QUEUED = 'queued';
    const STATUS_TRANSLATING = 'translating';
    const STATUS_DONE = 'done';
    const STATUS_ERROR = 'error';
    
    /**
     * Array with all possible status values
     */
    const STATUS_VALUES = [self::STATUS_QUEUED, self::STATUS_TRANSLATING, self::STATUS_DONE, self::STATUS_ERROR];

    /**
     * The unique identifier of the document
     * @var string
     */
    public string $documentId = '';

    /**
     * The status of the document (queued, translating or done).
     * See also: self::STATUS_VALUES
     *
     * @var string
     */
    public string $status = '';

    /**
     * How many characters were billed for this document?
     *
     * @var int
     */
    public int $billedCharacters = 0;

    /**
     * @var int|null
     */
    public int|null $secondsRemaining = null;

}
