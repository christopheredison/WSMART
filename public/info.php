<?php

exec('export PGPASSWORD="AskYourAdmin" && createdb -h localhost -p 5432 -U webmin rms_db2 2>&1', $result, $return);

echo json_encode($result);

