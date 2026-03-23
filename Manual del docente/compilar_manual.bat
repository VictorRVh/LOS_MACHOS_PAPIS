@echo off
setlocal
cd /d "%~dp0"
set "PATH=%PATH:C:\composer\composer.bat;=%"
set "PATH=%PATH:;C:\composer\composer.bat=%"
set "PATH=%PATH:C:\composer\composer.bat=%"
"C:\Users\willi\AppData\Local\Programs\MiKTeX\miktex\bin\x64\pdflatex.exe" -interaction=nonstopmode -halt-on-error cetpro-puno-manual-para-el-docente.tex
"C:\Users\willi\AppData\Local\Programs\MiKTeX\miktex\bin\x64\pdflatex.exe" -interaction=nonstopmode -halt-on-error cetpro-puno-manual-para-el-docente.tex
endlocal
