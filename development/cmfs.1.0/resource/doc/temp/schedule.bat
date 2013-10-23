@ECHO OFF
SCHTASKS /delete /tn batchupload /F
SCHTASKS /create /s "localhost" /RU "SYSTEM" /sc minute /mo 10 /tn batchupload /tr "C:\mcmconverter\server\php\php.exe -f C:\mcmconverter\htdocs\operator\cronjob\batchupload.php" /RL HIGHEST /F
SCHTASKS /run /tn batchupload /F
CLS
EXIT
