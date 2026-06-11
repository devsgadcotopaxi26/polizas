<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AseguradoraSeeder extends Seeder
{
    public function run(): void
    {
        // Insertar ciudades
        $ciudades = [
            ['id' => 1, 'nombre' => 'Quito', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nombre' => 'Guayaquil', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nombre' => 'Ambato', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nombre' => 'Manta', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nombre' => 'Cuenca', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->updateOrInsert(['id' => $ciudad['id']], $ciudad);
        }

        // Limpiar aseguradoras existentes
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('aseguradoras')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Insertar aseguradoras del SQL
        $aseguradoras = [
            [
                'nombre_empresa' => 'EQUISUIZA SEGUROS S.A.',
                'ciudad_id' => 1,
                'correo1' => 'ccordova@equisuiza.com',
                'correo2' => 'adamian@equisuiza.com',
                'correo3' => 'eillanez@equisuiza.com',
                'correo4' => 'mmayanquer@equisuiza.com',
                'correo5' => 'lpaez@equisuiza.com',
                'celular1' => '0987295076',
                'celular2' => '0969079604',
                'telefono_fijo1' => '023984000',
                'extensiones' => 'op. 3 op. 2',
                'nombre_contacto' => 'Alfredo Aguierre',
            ],
            [
                'nombre_empresa' => 'SEGUROS CONFIANZA',
                'ciudad_id' => 2,
                'correo1' => 'renovaciones.fianzas@confianza.com.ec',
                'correo2' => 'katiuska.simisterra@confianza.com.ec',
                'correo3' => 'javier.roca@confianza.com.ec',
                'correo4' => 'luis.manobanda@confianza.com.ec',
                'telefono_fijo1' => '042380680',
            ],
            [
                'nombre_empresa' => 'EQUISUIZA SEGUROS S.A.',
                'ciudad_id' => 3,
                'correo1' => 'adamian@equisuiza.com',
                'correo2' => 'scmarino@equisuiza.com',
                'celular1' => '0984127306',
                'telefono_fijo1' => '032398400',
                'extensiones' => '5026 - 5012',
                'nombre_contacto' => 'Cristina Mariño',
            ],
            [
                'nombre_empresa' => 'SWEADEN SEGUROS CIA LTDA',
                'ciudad_id' => null,
                'correo1' => 'gvillafuerte@sweadenseguros.com',
                'correo2' => 'vgarzon@sweadenseguros.com',
                'correo3' => 'aalmea@sweadenseguros.com',
                'correo4' => 'gmmiranda@sweadenseguros.com',
                'telefono_fijo1' => '032824231',
                'telefono_fijo2' => '032426080',
            ],
            [
                'nombre_empresa' => 'INTEROCEANICA DE SEGUROS',
                'ciudad_id' => 1,
                'correo1' => 'paola.aguilar@segurosinteroceanica.com',
                'correo2' => 'erika.valdospinos@segurosinteroceanica.com',
                'correo3' => 'renovaciones.fianzas@segurosinteroceanica.com',
                'telefono_fijo1' => '022251400',
                'extensiones' => '7567, 7562',
                'nombre_contacto' => 'Paola Aguilar',
            ],
            [
                'nombre_empresa' => 'ASEGURADORA DEL SUR',
                'ciudad_id' => 3,
                'correo1' => 'fianzasrenovaciones@asur.ec',
                'correo2' => 'carlos.rodriguez@asur.ec',
                'correo3' => 'alejandra.rosero@asur.ec',
                'correo4' => 'karen.meneses@asur.ec',
                'correo5' => 'ainary.alfonso@asur.ec',
                'celular1' => '0992755618',
                'celular2' => '0993323106',
                'celular3' => '0993695052',
                'telefono_fijo1' => '032828211',
                'telefono_fijo2' => '032829299',
                'extensiones' => '7107',
            ],
            [
                'nombre_empresa' => 'LATINA SEGUROS',
                'ciudad_id' => 1,
                'correo1' => 'monica.morales@latinaseguros.com.ec',
                'correo2' => 'diego.herrera@latinaseguros.com.ec',
                'telefono_fijo1' => '023948380',
                'extensiones' => '120',
                'nombre_contacto' => 'Diego Herrera',
            ],
            [
                'nombre_empresa' => 'SEGUROS INTEROCEANICA',
                'ciudad_id' => 3,
                'correo1' => 'alex.rosero@segurosinteroceanica.com',
                'correo2' => 'ana.velastegui@segurosinteroceanica.com',
                'celular1' => '0986115405',
            ],
            [
                'nombre_empresa' => 'EQUISUIZA SEGUROS S.A.',
                'ciudad_id' => 2,
                'correo1' => 'rfranco@equisuiza.com',
                'correo2' => 'sesa_sbs@equisuiza.com',
                'correo3' => 'jmerchan@equisuiza.com',
                'telefono_fijo1' => '043722500',
                'telefono_fijo2' => '023984000',
                'extensiones' => '2222-2220-4008',
            ],
            [
                'nombre_empresa' => 'SEGUROS CONSTITUCION',
                'ciudad_id' => null,
                'correo1' => 'rreinoso@segurosconstitucion.com.ec',
                'correo2' => 'mtituana@segurosconstitucion.com.ec',
                'telefono_fijo1' => '023982170',
                'telefono_fijo2' => '023982000',
                'extensiones' => '2597, 2599',
                'nombre_contacto' => 'Renan Reinoso',
            ],
            [
                'nombre_empresa' => 'LATINA SEGUROS',
                'ciudad_id' => 3,
                'correo1' => 'lorena.escobar@latinaseguros.com.ec',
                'telefono_fijo1' => '032420046',
                'telefono_fijo2' => '032826472',
                'extensiones' => '108',
            ],
            [
                'nombre_empresa' => 'SEGUROS CONFIANZA',
                'ciudad_id' => 1,
                'correo1' => 'renovaciones.fianzas@confianza.com.ec',
                'telefono_fijo1' => '022465816',
                'telefono_fijo2' => '022465817',
            ],
            [
                'nombre_empresa' => 'SWEADEN SEGUROS CIA LTDA',
                'ciudad_id' => 1,
                'correo1' => 'ksoto@sweadenseguros.com',
                'correo2' => 'ichicaiza@sweadenseguros.com',
                'celular1' => '0999160040',
                'nombre_contacto' => 'Katy Soto',
            ],
            [
                'nombre_empresa' => 'ASEGURADORA DEL SUR',
                'ciudad_id' => 1,
                'correo1' => 'fianzasrenovaciones@asur.ec',
                'correo2' => 'stalin.taticuan@asur.ec',
                'correo3' => 'amed.gavilanes@asur.ec',
                'correo4' => 'amparo.romero@asur.ec',
                'celular1' => '0993695048',
                'telefono_fijo1' => '022997527',
                'telefono_fijo2' => '022997528',
                'extensiones' => '6342-6513',
            ],
            [
                'nombre_empresa' => 'SEGUROS CONFIANZA',
                'ciudad_id' => 4,
                'correo1' => 'glenis.rengifo@confianza.com.ec',
                'correo2' => 'valentina.espinales@confianza.com.ec',
                'correo3' => 'alejandra.teran@confianza.com.ec',
                'correo4' => 'renovaciones.fianzas@confianza.com.ec',
                'telefono_fijo1' => '053905055',
                'telefono_fijo2' => '053905056',
            ],
            [
                'nombre_empresa' => 'SEGUROS CONFIANZA',
                'ciudad_id' => 5,
                'correo1' => 'renovaciones.fianzas@confianza.com.ec',
                'telefono_fijo1' => '072812052',
            ],
            [
                'nombre_empresa' => 'LATINA SEGUROS',
                'ciudad_id' => 2,
                'telefono_fijo1' => '042590500',
            ],
            [
                'nombre_empresa' => 'BANCO BOLIVARIANO',
                'ciudad_id' => null,
            ],
            [
                'nombre_empresa' => 'SWEADEN SEGUROS CIA LTDA',
                'ciudad_id' => null,
                'telefono_fijo1' => '032800418',
                'telefono_fijo2' => '032800416',
            ],
        ];

        foreach ($aseguradoras as $data) {
            $data['activo'] = true;
            $data['created_at'] = now();
            $data['updated_at'] = now();
            DB::table('aseguradoras')->insert($data);
        }
    }
}
