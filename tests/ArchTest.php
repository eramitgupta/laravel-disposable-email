<?php

arch()->preset()->php();

arch()->preset()->security();

arch()
    ->expect(['dd', 'dump', 'ray', 'die', 'var_dump', 'ds', 'print_f'])
    ->not->toBeUsed();
