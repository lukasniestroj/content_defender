<?php

declare(strict_types=1);

namespace IchHabRecht\ContentDefender\Tests\Unit\Repository;

/*
 * This file is part of the TYPO3 extension content_defender.
 *
 * (c) Nicole Cordes <typo3@cordes.co>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use IchHabRecht\ContentDefender\Repository\ContentRepository;
use Nimut\TestingFramework\TestCase\UnitTestCase;

class ContentRepositoryTest extends UnitTestCase
{
    /**
     * @var ContentRepository
     */
    protected $subject;

    /**
     * @var array
     */
    protected $record = [
        'pid' => 1,
        'colPos' => 0,
        'sys_language_uid' => 0,
        'uid' => 4,
    ];

    protected function setUp()
    {
        parent::setUp();

        $GLOBALS['TCA']['tt_content']['ctrl']['languageField'] = 'sys_language_uid';

        $subject = $this->getMockBuilder(ContentRepository::class)
            ->setMethods(['fetchRecordsForColpos'])
            ->getMock();
        $subject->expects($this->once())
            ->method('fetchRecordsForColpos')
            ->willReturn([
                1 => 1,
                2 => 2,
                3 => 3,
            ]);

        \Closure::bind(function () use ($subject) {
            $subject::$colPosCount = [];
        }, null, ContentRepository::class)();

        $this->subject = $subject;
    }

    /**
     * @test
     */
    public function countColPosByRecordReturnsCountOfRecordsInCurrentColPos()
    {
        $this->assertSame(3, $this->subject->countColPosByRecord($this->record));
    }

    /**
     * @test
     */
    public function addRecordToColPosReturnsNewCountOfRecordsInCurrentColPos()
    {
        $this->assertSame(4, $this->subject->addRecordToColPos($this->record));
    }

    /**
     * @test
     */
    public function isRecordInColPosReturnsTrueForRecordInColPos()
    {
        $record = $this->record;
        $record['uid'] = 1;

        $this->assertTrue($this->subject->isRecordInColPos($record));
    }

    /**
     * @test
     */
    public function isRecordInColPosReturnsFalseForRecordNotInColPos()
    {
        $this->assertFalse($this->subject->isRecordInColPos($this->record));
    }

    /**
     * @test
     */
    public function substituteNewIdsWithUidsReplacesNewIdsWithUids()
    {
        $record = $this->record;
        $record['uid'] = 'NEW123';

        $this->subject->addRecordToColPos($record);
        $this->subject->substituteNewIdsWithUids(['NEW123' => $this->record['uid']]);

        $this->assertSame(4, $this->subject->countColPosByRecord($this->record));
        $this->assertTrue($this->subject->isRecordInColPos($this->record));

        $record['uid'] = 1;
        $this->assertTrue($this->subject->isRecordInColPos($record));
    }
}
