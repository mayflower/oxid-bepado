<?php

/**
 * Base article number generator.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_article_number_generator
{
    /**
     * We just creating a some random numbers in here.
     */
    public function generate()
    {
        return 'BEP-' . mt_rand(0,9999) . '-' . mt_rand(0,9999);
    }
}
