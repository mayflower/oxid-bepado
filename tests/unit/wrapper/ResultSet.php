<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class ResultSet
{
    public $EOF = false;

    public $fields = array('p_source_id' => 'test-id');

    public function moveNext()
    {
        $this->EOF = true;
    }
}
