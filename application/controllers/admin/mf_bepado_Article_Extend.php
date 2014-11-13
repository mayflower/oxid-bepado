<?php
/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_bepado_Article_Extend extends mf_bepado_Article_Extend_parent
{
    /**
     * We need to add a check for the bepado export option after saving the article.
     */
    public function save()
    {
        parent::save();

        return;
    }

}
 