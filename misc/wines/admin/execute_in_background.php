<?php

if (isset($targetScript) && file_exists($targetScript))
{
    $output = shell_exec("/usr/bin/nohup php $targetScript > $targetScript.log 2> $targetScript.err &");

    echo "Executing $targetScript in background...";
}
else
{
    echo 'Cannot find a script to execute.';
}
