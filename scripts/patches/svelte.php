<?php

return [

    '(?<=(if|key|then|catch|snippet|html|render).*?)\\\\G' => '(?<=(if|key|then|catch|snippet|html|render).{0,248}?)\\\\G',

    '(?<=const.*?)\\\\G' => '(?<=const.{0,249}?)\\\\G',

    '(?<=each.*?)\\\\G' => '(?<=each.{0,250}?)\\\\G',

    '(?<=await.*?)\\\\G' => '(?<=await.{0,249}?)\\\\G',

    '(?<=debug.*?)\\\\G' => '(?<=debug.{0,249}?)\\\\G',

];
