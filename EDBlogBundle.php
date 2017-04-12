<?php

namespace ED\BlogBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class EDBlogBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
