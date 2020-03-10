@echo off
echo "Starting build..."
rd /s /q .build
mkdir .build
copy *.* .build
del ".build\.*"
del ".build\build.bat"
xcopy /S /Y /I language .build\language
xcopy /S /Y /I images .build\images
xcopy /S /Y /I vendor .build\vendor
xcopy /S /Y /I test .build\test

echo "Creating zip file..."
cd .build
setlocal EnableDelayedExpansion
FOR /F "skip=1 tokens=1-6" %%A IN ('WMIC Path Win32_LocalTime Get Day^,Hour^,Minute^,Month^,Second^,Year /Format:table') DO (
    if "%%B" NEQ "" (
        SET /A FDATE=%%F*10000+%%D*100+%%A
        SET /A FTIME=%%B*10000+%%C*100+%%E
    )
)

call "C:\Program Files\7-Zip\7z.exe" a -y -r -aoa ..\.releases\bearsonsave-J3.9.%FDATE%.zip *.*
cd ..
rd /s /q .build
echo "Done..."
