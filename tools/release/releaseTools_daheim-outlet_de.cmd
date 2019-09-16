@echo off

echo ***************************************
echo ** Translation ...                   **
echo ***************************************
call ..\translator\makeLanguagePackage.cmd



echo ***************************************
echo ** copy files ...                    **
echo ***************************************
set pubdir=srx-%date%
set productname=daheim-outlet_de

if exist products\%pubdir% rd /s/q products\%pubdir%
md products\%pubdir%

md products\%pubdir%\api
md products\%pubdir%\ext
md products\%pubdir%\resources

copy ..\..\*.* products\%pubdir%\
xcopy ..\..\src\api products\%pubdir%\api\ /E
xcopy ..\..\src\resources products\%pubdir%\resources\ /E
xcopy ..\callback\%productname% products\%pubdir%\ /E/Y
del products\%pubdir%\api\tmp\cache\mpdels\*.* /q
del products\%pubdir%\api\tmp\cache\persistent\*.* /q
del products\%pubdir%\api\tmp\logs\*.* /q

xcopy ..\..\src\ext products\%pubdir%\ext\ /E



echo ***************************************
echo ** build EXTJS ...                   **
echo ***************************************
cd products\%pubdir%\ext
sencha app build 
cd ..\..\..\

echo ***************************************
echo ** delete ext ...                   **
echo ***************************************
move products\%pubdir%\ext\build\production\SRX products\%pubdir%\srx
rd /s/q products\%pubdir%\ext

echo ***************************************
echo ** The build process was completed!  **
echo ** make zip ...                      **
echo ***************************************
if exist products\%pubdir%.zip del products\%pubdir%.zip
php makezip.php products\%pubdir% products\%pubdir%.zip
rd /s/q products\%pubdir%

echo 
pause