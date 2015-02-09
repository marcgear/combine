<?php
namespace Combine;


use Doctrine\DBAL\Connection;

class EntryGateway
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function loadEntriesForUser($username, $cutOff)
    {
        $sql = 'SELECT
              question.*,
              entry.response as answer,
              entry.id as entry_id
            FROM question
              LEFT JOIN entry ON entry.question_id = question.id
                AND entry.username = ?
                AND entry.created > ?
            WHERE question.active = 1';

        $questions = $this->conn->fetchAll($sql, array(
            $username,
            date('Y-m-d H:i:s', $cutOff),
        ));

        return $questions;
    }

    public function loadQuestionCount()
    {
        $sql = 'SELECT count(id) FROM question WHERE active = 1';
        return $this->conn->fetchColumn($sql);
    }

    public function userHasSubmitted($username, $cutOff)
    {
        $sql = 'SELECT
                  entry.response as answer,
                  entry.id as entry_id
                FROM entry, question
                WHERE entry.question_id = question.id
                  AND entry.username = ?
                  AND entry.created > ?
                  AND question.active = 1';

        $responses = $this->conn->fetchAll($sql, array(
            $username,
            date('Y-m-d H:i:s', $cutOff),
        ));

        return ($this->loadQuestionCount() == count($responses));
    }

    public function loadEntriesForPeriod($start, $end)
    {
        $sql = 'SELECT
                entry.*,
                question.question,
                user.username,
                user.name,
                user.department
            FROM entry, question, user
            WHERE entry.question_id = question.id
                AND entry.username = user.username
                AND entry.created BETWEEN ? AND ?
            ORDER BY entry.question_id, user.department';

        $res = $this->conn->fetchAll($sql, array(
            date('Y-m-d H:i:s', (int) $start),
            date('Y-m-d H:i:s', (int) $end),
        ));
        $questions = array();
        foreach ($res as $entry) {
            if (!isset($questions[$entry['question_id']])) {
                $questions[$entry['question_id']] = array(
                    'question' => $entry['question'],
                    'entries'  => array(),
                );
            }
            $questions[$entry['question_id']]['entries'][] = $entry;
        }
        return $questions;
    }

    public function saveEntry($answer, $questionId, $username, $entryId = null)
    {
        if ($entryId) {
            $sql = 'UPDATE entry SET response = ?  WHERE id = ? AND question_id = ? AND username = ? ';
        } else {
            $sql = 'INSERT INTO entry (response, id, created, question_id, username) VALUES (?, ?, NOW(),?,?)';
        }

        $this->conn->executeUpdate($sql, array(
            $answer,
            $entryId,
            (int) $questionId,
            $username,
        ));
    }
}