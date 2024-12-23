@echo off

cd /d %~dp0

call %USERPROFILE%\user-config.bat

for %%I in (.) do set PLUGINNAME=%%~nxI

set ARCHIVENAME=%PLUGINNAME%.zip

echo.
echo #################################################################
echo   %PLUGINNAME% -^> %ARCHIVENAME%
echo #################################################################

if exist %ARCHIVENAME% del %ARCHIVENAME%

mkdir .\%PLUGINNAME%
xcopy .\*.txt .\%PLUGINNAME% /y
xcopy .\*.md .\%PLUGINNAME% /y
xcopy .\*.php .\%PLUGINNAME% /y

%SZIPDIR%\7z.exe a -tzip -y .\%ARCHIVENAME% .\%PLUGINNAME%

rmdir /S /Q .\%PLUGINNAME%

wput.exe -nc -u %ARCHIVENAME% ftp://%WEBSPACE_FTP_USER%:%WEBSPACE_FTP_PASS%@%WEBSPACE_FTP_HOST%/html/main/downloads/files/dokuwiki/

:end
echo.
