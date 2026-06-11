$python = "C:\laragon\bin\python\python-3.13\python.exe"
$script = "C:\laragon\www\polizas\sign_pyhanko.py"
$input  = "C:\laragon\www\polizas\storage\app\private\oficios\Oficio_Renovacion_4.pdf"
$output = "C:\laragon\www\polizas\storage\app\Oficio_TEST_TSA_v2.pdf"
$p12    = "C:\Users\GESTOR TICS 2\OneDrive\Documents\ANTHONY SEBASTIAN PENA GUACHIMBOZA 0550614788-070126115624.p12"

& $python $script --input $input --output $output --p12 $p12 --password "Anthony1998" --name "ANTHONY PENA" --tsa-url "https://freetsa.org/tsr" --app-version "1.0.0" --app-name "SisPolizas-GADPC"
