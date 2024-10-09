<?php

return [

    '(?<=^\\\\S+)=' => '=',

    '(?<=^Version.*)\\\\d+(\\\\.{0,1}\\\\d*)' => '\\\\d+(\\\\.{0,1}\\\\d*)',

    '(?<=^Exec.*\\\\s)-+\\\\S+' => '-+\\\\S+',

    '(?<=^Exec.*)\\\\s\\\\%[fFuUick]\\\\s' => '\\\\s\\\\%[fFuUick]\\\\s',

    '(?<=^Categories.*)AudioVideo|(?<=^Categories.*)Audio|(?<=^Categories.*)Video|(?<=^Categories.*)Development|(?<=^Categories.*)Education|(?<=^Categories.*)Game|(?<=^Categories.*)Graphics|(?<=^Categories.*)Network|(?<=^Categories.*)Office|(?<=^Categories.*)Science|(?<=^Categories.*)Settings|(?<=^Categories.*)System|(?<=^Categories.*)Utility' => 'AudioVideo|Audio|Video|Development|Education|Game|Graphics|Network|Office|Science|Settings|System|Utility',

];
