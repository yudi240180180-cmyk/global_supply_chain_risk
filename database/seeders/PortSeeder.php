<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\Port;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        // Format: [name, locode, country_name, lat, lng, type, function]
        $ports = [

            // =============================
            // INDONESIA (21 ports)
            // =============================
            ['Tanjung Priok',        'IDTPP', 'Indonesia',  -6.095,   106.886,  'Sea', 'Import / Export'],
            ['Tanjung Perak',        'IDSUB', 'Indonesia',  -7.204,   112.733,  'Sea', 'Import / Export'],
            ['Belawan',              'IDBLW', 'Indonesia',   3.784,    98.694,  'Sea', 'Import / Export'],
            ['Makassar',             'IDMKS', 'Indonesia',  -5.133,   119.412,  'Sea', 'Import / Export'],
            ['Balikpapan',           'IDBPN', 'Indonesia',  -1.267,   116.829,  'Sea', 'Import / Export'],
            ['Bitung',               'IDBIT', 'Indonesia',   1.441,   125.198,  'Sea', 'Import / Export'],
            ['Palembang',            'IDPLM', 'Indonesia',  -2.916,   104.745, 'River','Import / Export'],
            ['Pontianak',            'IDPNK', 'Indonesia',  -0.022,   109.333, 'River','Import / Export'],
            ['Banjarmasin',          'IDBJM', 'Indonesia',  -3.322,   114.590,  'Sea', 'Import / Export'],
            ['Samarinda',            'IDSRI', 'Indonesia',  -0.502,   117.153, 'River','Cargo'],
            ['Batam',                'IDBAT', 'Indonesia',   1.121,   104.040,  'Sea', 'Import / Export'],
            ['Ambon',                'IDAMQ', 'Indonesia',  -3.693,   128.183,  'Sea', 'Import / Export'],
            ['Sorong',               'IDSOH', 'Indonesia',  -0.876,   131.261,  'Sea', 'Cargo'],
            ['Kupang',               'IDKOE', 'Indonesia',  -10.179,  123.607,  'Sea', 'Cargo'],
            ['Padang',               'IDPDK', 'Indonesia',  -0.947,   100.354,  'Sea', 'Import / Export'],
            ['Panjang',              'IDPNJ', 'Indonesia',  -5.471,   105.287,  'Sea', 'Import / Export'],
            ['Cirebon',              'IDCBN', 'Indonesia',  -6.731,   108.552,  'Sea', 'Cargo'],
            ['Lembar',               'IDLEM', 'Indonesia',  -8.722,   116.068,  'Sea', 'Cargo'],
            ['Kendari',              'IDKEN', 'Indonesia',  -3.972,   122.512,  'Sea', 'Cargo'],
            ['Merak',                'IDMRK', 'Indonesia',  -5.928,   105.999,  'Sea', 'Cargo'],
            ['Dumai',                'IDDUI', 'Indonesia',   1.677,   101.448,  'Sea', 'Import / Export'],

            // =============================
            // SINGAPORE (3 ports)
            // =============================
            ['Port of Singapore',    'SGSIN', 'Singapore',   1.264,   103.841,  'Sea', 'Import / Export'],
            ['Jurong Port',          'SGJUR', 'Singapore',   1.288,   103.707,  'Sea', 'Cargo'],
            ['Sembawang Wharves',    'SGSEM', 'Singapore',   1.441,   103.818,  'Sea', 'Cargo'],

            // =============================
            // MALAYSIA (10 ports)
            // =============================
            ['Port Klang',           'MYPKG', 'Malaysia',    3.001,   101.399,  'Sea', 'Import / Export'],
            ['Tanjung Pelepas',      'MYTPP', 'Malaysia',    1.363,   103.548,  'Sea', 'Import / Export'],
            ['Penang Port',          'MYPEN', 'Malaysia',    5.416,   100.338,  'Sea', 'Import / Export'],
            ['Johor Port',           'MYJHB', 'Malaysia',    1.468,   103.793,  'Sea', 'Import / Export'],
            ['Kuantan Port',         'MYKUA', 'Malaysia',    3.831,   103.338,  'Sea', 'Cargo'],
            ['Pasir Gudang',         'MYPGU', 'Malaysia',    1.472,   103.906,  'Sea', 'Cargo'],
            ['Lumut Port',           'MYLUM', 'Malaysia',    4.232,   100.632,  'Sea', 'Cargo'],
            ['Bintulu Port',         'MYBTU', 'Malaysia',    3.170,   113.046,  'Sea', 'Import / Export'],
            ['Sandakan Port',        'MYSDK', 'Malaysia',    5.841,   118.117,  'Sea', 'Cargo'],
            ['Kota Kinabalu Port',   'MYBKI', 'Malaysia',    5.978,   116.074,  'Sea', 'Cargo'],

            // =============================
            // CHINA (25 ports)
            // =============================
            ['Port of Shanghai',     'CNSHA', 'China',      31.230,   121.474,  'Sea', 'Import / Export'],
            ['Port of Ningbo',       'CNNBO', 'China',      29.868,   121.544,  'Sea', 'Import / Export'],
            ['Port of Shenzhen',     'CNSZX', 'China',      22.543,   114.058,  'Sea', 'Import / Export'],
            ['Port of Guangzhou',    'CNGZH', 'China',      23.129,   113.264,  'Sea', 'Import / Export'],
            ['Port of Qingdao',      'CNTAO', 'China',      36.067,   120.383,  'Sea', 'Import / Export'],
            ['Port of Tianjin',      'CNTXG', 'China',      39.084,   117.201,  'Sea', 'Import / Export'],
            ['Port of Dalian',       'CNDLC', 'China',      38.914,   121.602,  'Sea', 'Import / Export'],
            ['Port of Xiamen',       'CNXMN', 'China',      24.479,   118.089,  'Sea', 'Import / Export'],
            ['Port of Lianyungang',  'CNLYG', 'China',      34.750,   119.414,  'Sea', 'Import / Export'],
            ['Port of Zhoushan',     'CNZOS', 'China',      29.986,   122.207,  'Sea', 'Import / Export'],
            ['Port of Wuhan',        'CNWUH', 'China',      30.593,   114.305, 'River','Cargo'],
            ['Port of Chongqing',    'CNCKG', 'China',      29.563,   106.551, 'River','Cargo'],
            ['Port of Suzhou',       'CNSZV', 'China',      31.299,   120.585,  'Sea', 'Cargo'],
            ['Port of Nanjing',      'CNNKG', 'China',      32.060,   118.797, 'River','Import / Export'],
            ['Port of Yantai',       'CNYNT', 'China',      37.464,   121.448,  'Sea', 'Import / Export'],
            ['Port of Fuzhou',       'CNFOC', 'China',      26.061,   119.306,  'Sea', 'Import / Export'],
            ['Port of Nantong',      'CNNTG', 'China',      31.980,   120.893,  'Sea', 'Cargo'],
            ['Port of Tangshan',     'CNTAN', 'China',      39.633,   118.180,  'Sea', 'Cargo'],
            ['Port of Yingkou',      'CNYIK', 'China',      40.663,   122.228,  'Sea', 'Cargo'],
            ['Port of Zhanjiang',    'CNZJG', 'China',      21.271,   110.364,  'Sea', 'Cargo'],
            ['Port of Huangpu',      'CNHPU', 'China',      23.083,   113.433,  'Sea', 'Cargo'],
            ['Port of Shantou',      'CNSTO', 'China',      23.353,   116.682,  'Sea', 'Cargo'],
            ['Port of Wenzhou',      'CNWNZ', 'China',      28.015,   120.672,  'Sea', 'Cargo'],
            ['Port of Haikou',       'CNHAK', 'China',      20.044,   110.342,  'Sea', 'Cargo'],
            ['Port of Sanya',        'CNSYX', 'China',      18.252,   109.512,  'Sea', 'Cargo'],

            // =============================
            // SOUTH KOREA (8 ports)
            // =============================
            ['Port of Busan',        'KRPUS', 'South Korea', 35.103,  129.040,  'Sea', 'Import / Export'],
            ['Port of Incheon',      'KRINC', 'South Korea', 37.456,  126.705,  'Sea', 'Import / Export'],
            ['Port of Gwangyang',    'KRKWJ', 'South Korea', 34.940,  127.700,  'Sea', 'Import / Export'],
            ['Port of Ulsan',        'KRULN', 'South Korea', 35.537,  129.358,  'Sea', 'Import / Export'],
            ['Port of Pyeongtaek',   'KRPTK', 'South Korea', 36.960,  126.880,  'Sea', 'Cargo'],
            ['Port of Masan',        'KRMKN', 'South Korea', 35.200,  128.583,  'Sea', 'Cargo'],
            ['Port of Pohang',       'KRKPO', 'South Korea', 36.019,  129.365,  'Sea', 'Cargo'],
            ['Port of Donghae',      'KRTGH', 'South Korea', 37.524,  129.114,  'Sea', 'Cargo'],

            // =============================
            // JAPAN (15 ports)
            // =============================
            ['Port of Yokohama',     'JPYOK', 'Japan',      35.444,   139.638,  'Sea', 'Import / Export'],
            ['Port of Kobe',         'JPUKB', 'Japan',      34.690,   135.196,  'Sea', 'Import / Export'],
            ['Port of Tokyo',        'JPTYO', 'Japan',      35.676,   139.650,  'Sea', 'Import / Export'],
            ['Port of Osaka',        'JPOSA', 'Japan',      34.694,   135.502,  'Sea', 'Import / Export'],
            ['Port of Nagoya',       'JPNGJ', 'Japan',      35.039,   136.886,  'Sea', 'Import / Export'],
            ['Port of Hakata',       'JPHKT', 'Japan',      33.600,   130.368,  'Sea', 'Import / Export'],
            ['Port of Shimizu',      'JPSMZ', 'Japan',      35.009,   138.512,  'Sea', 'Cargo'],
            ['Port of Kitakyushu',   'JPKIJ', 'Japan',      33.883,   130.883,  'Sea', 'Cargo'],
            ['Port of Chiba',        'JPCHI', 'Japan',      35.605,   140.123,  'Sea', 'Cargo'],
            ['Port of Kawasaki',     'JPKWS', 'Japan',      35.519,   139.702,  'Sea', 'Cargo'],
            ['Port of Hiroshima',    'JPHIJ', 'Japan',      34.385,   132.455,  'Sea', 'Cargo'],
            ['Port of Sendai',       'JPSDS', 'Japan',      38.265,   141.022,  'Sea', 'Cargo'],
            ['Port of Otaru',        'JPOTK', 'Japan',      43.196,   141.004,  'Sea', 'Cargo'],
            ['Port of Hakodate',     'JPHKD', 'Japan',      41.768,   140.729,  'Sea', 'Cargo'],
            ['Port of Yokkaichi',    'JPYKK', 'Japan',      34.964,   136.625,  'Sea', 'Cargo'],

            // =============================
            // INDIA (18 ports)
            // =============================
            ['Nhava Sheva (JNPT)',   'INNSA', 'India',      18.949,    72.950,  'Sea', 'Import / Export'],
            ['Chennai Port',         'INMAA', 'India',      13.083,    80.271,  'Sea', 'Import / Export'],
            ['Mumbai Port',          'INBOM', 'India',      18.960,    72.840,  'Sea', 'Import / Export'],
            ['Mundra Port',          'INMUN', 'India',      22.839,    69.703,  'Sea', 'Import / Export'],
            ['Kolkata Port',         'INCCU', 'India',      22.572,    88.362, 'River','Import / Export'],
            ['Vishakhapatnam Port',  'INVTZ', 'India',      17.686,    83.218,  'Sea', 'Import / Export'],
            ['Cochin Port',          'INCOK', 'India',       9.967,    76.271,  'Sea', 'Import / Export'],
            ['Kandla Port',          'INKLA', 'India',      23.003,    70.220,  'Sea', 'Import / Export'],
            ['Tuticorin Port',       'INTUT', 'India',       8.798,    78.149,  'Sea', 'Cargo'],
            ['New Mangalore Port',   'INMRM', 'India',      12.917,    74.817,  'Sea', 'Cargo'],
            ['Paradip Port',         'INPBH', 'India',      20.317,    86.617,  'Sea', 'Cargo'],
            ['Haldia Port',          'INHLD', 'India',      22.033,    88.065,  'Sea', 'Cargo'],
            ['Kamarajar Port',       'INKAT', 'India',      13.234,    80.317,  'Sea', 'Cargo'],
            ['Deendayal Port',       'INKND', 'India',      23.003,    70.220,  'Sea', 'Cargo'],
            ['Mormugao Port',        'INMRM', 'India',      15.417,    73.800,  'Sea', 'Cargo'],
            ['NMPA Panambur',        'INPNM', 'India',      12.927,    74.803,  'Sea', 'Cargo'],
            ['Ennore Port',          'INENN', 'India',      13.234,    80.317,  'Sea', 'Cargo'],
            ['Dighi Port',           'INDGH', 'India',      18.217,    73.033,  'Sea', 'Cargo'],

            // =============================
            // AUSTRALIA (12 ports)
            // =============================
            ['Port Botany',          'AUBTB', 'Australia',  -33.966,  151.226,  'Sea', 'Import / Export'],
            ['Port of Melbourne',    'AUMEL', 'Australia',  -37.814,  144.963,  'Sea', 'Import / Export'],
            ['Port of Brisbane',     'AUBNE', 'Australia',  -27.470,  153.025,  'Sea', 'Import / Export'],
            ['Port of Fremantle',    'AUFRE', 'Australia',  -32.053,  115.745,  'Sea', 'Import / Export'],
            ['Port of Adelaide',     'AUADL', 'Australia',  -34.836,  138.500,  'Sea', 'Import / Export'],
            ['Port Hedland',         'AUPHD', 'Australia',  -20.310,  118.569,  'Sea', 'Cargo'],
            ['Port of Dampier',      'AUDMP', 'Australia',  -20.667,  116.717,  'Sea', 'Cargo'],
            ['Port of Darwin',       'AUDAW', 'Australia',  -12.462,  130.844,  'Sea', 'Cargo'],
            ['Port of Townsville',   'AUTSV', 'Australia',  -19.258,  146.818,  'Sea', 'Cargo'],
            ['Port of Newcastle',    'AUEWC', 'Australia',  -32.924,  151.787,  'Sea', 'Cargo'],
            ['Port of Gladstone',    'AUGLT', 'Australia',  -23.844,  151.259,  'Sea', 'Cargo'],
            ['Port of Geraldton',    'AUGET', 'Australia',  -28.778,  114.614,  'Sea', 'Cargo'],

            // =============================
            // GERMANY (7 ports)
            // =============================
            ['Port of Hamburg',      'DEHAM', 'Germany',    53.546,     9.966,  'Sea', 'Import / Export'],
            ['Bremerhaven',          'DEBRV', 'Germany',    53.540,     8.581,  'Sea', 'Import / Export'],
            ['Port of Bremen',       'DOBRE', 'Germany',    53.075,     8.807, 'River','Cargo'],
            ['Port of Rostock',      'DERSK', 'Germany',    54.152,    12.100,  'Sea', 'Cargo'],
            ['Port of Duisburg',     'DEDUI', 'Germany',    51.435,     6.776, 'River','Cargo'],
            ['Port of Kiel',         'DEKEL', 'Germany',    54.323,    10.133,  'Sea', 'Cargo'],
            ['Port of Lübeck',       'DELBC', 'Germany',    53.866,    10.686,  'Sea', 'Cargo'],

            // =============================
            // NETHERLANDS (5 ports)
            // =============================
            ['Port of Rotterdam',    'NLRTM', 'Netherlands', 51.948,    4.134,  'Sea', 'Import / Export'],
            ['Port of Amsterdam',    'NLAMS', 'Netherlands', 52.374,    4.900,  'Sea', 'Import / Export'],
            ['Port of Moerdijk',     'NLMOE', 'Netherlands', 51.693,    4.621,  'Sea', 'Cargo'],
            ['Port of Vlissingen',   'NLVLI', 'Netherlands', 51.442,    3.576,  'Sea', 'Cargo'],
            ['Port of Terneuzen',    'NLTER', 'Netherlands', 51.336,    3.830,  'Sea', 'Cargo'],

            // =============================
            // BELGIUM (4 ports)
            // =============================
            ['Port of Antwerp',      'BEANR', 'Belgium',    51.260,     4.400,  'Sea', 'Import / Export'],
            ['Port of Ghent',        'BEGNE', 'Belgium',    51.037,     3.717,  'Sea', 'Cargo'],
            ['Port of Bruges',       'BEBRG', 'Belgium',    51.209,     3.224,  'Sea', 'Cargo'],
            ['Port of Liège',        'BELGG', 'Belgium',    50.633,     5.567, 'River','Cargo'],

            // =============================
            // FRANCE (8 ports)
            // =============================
            ['Port of Le Havre',     'FRLEH', 'France',     49.494,     0.108,  'Sea', 'Import / Export'],
            ['Port of Marseille',    'FRMRS', 'France',     43.296,     5.381,  'Sea', 'Import / Export'],
            ['Port of Dunkirk',      'FRDKK', 'France',     51.036,     2.373,  'Sea', 'Cargo'],
            ['Port of Bordeaux',     'FRBOD', 'France',     44.837,    -0.579, 'River','Cargo'],
            ['Port of Nantes',       'FRNTE', 'France',     47.218,    -1.554, 'River','Cargo'],
            ['Port of Rouen',        'FRRUO', 'France',     49.443,     1.099, 'River','Cargo'],
            ['Port of La Rochelle',  'FRLAR', 'France',     46.155,    -1.149,  'Sea', 'Cargo'],
            ['Port of Strasbourg',   'FRSTS', 'France',     48.573,     7.752, 'River','Cargo'],

            // =============================
            // SPAIN (10 ports)
            // =============================
            ['Port of Valencia',     'ESVLC', 'Spain',      39.470,    -0.376,  'Sea', 'Import / Export'],
            ['Port of Barcelona',    'ESBCN', 'Spain',      41.351,     2.171,  'Sea', 'Import / Export'],
            ['Port of Algeciras',    'ESALG', 'Spain',      36.131,    -5.452,  'Sea', 'Import / Export'],
            ['Port of Bilbao',       'ESBIO', 'Spain',      43.320,    -3.040,  'Sea', 'Import / Export'],
            ['Port of Las Palmas',   'ESLPA', 'Spain',      28.137,   -15.429,  'Sea', 'Import / Export'],
            ['Port of Cartagena',    'ESACT', 'Spain',      37.600,    -0.983,  'Sea', 'Cargo'],
            ['Port of Huelva',       'ESHUE', 'Spain',      37.261,    -6.944,  'Sea', 'Cargo'],
            ['Port of Gijón',        'ESGIO', 'Spain',      43.534,    -5.663,  'Sea', 'Cargo'],
            ['Port of Santander',    'ESSDR', 'Spain',      43.464,    -3.800,  'Sea', 'Cargo'],
            ['Port of Tarragona',    'ESTRG', 'Spain',      41.115,     1.261,  'Sea', 'Cargo'],

            // =============================
            // ITALY (10 ports)
            // =============================
            ['Port of Genoa',        'ITGOA', 'Italy',      44.406,     8.946,  'Sea', 'Import / Export'],
            ['Port of Gioia Tauro',  'ITGIT', 'Italy',      38.425,    15.895,  'Sea', 'Import / Export'],
            ['Port of La Spezia',    'ITLSP', 'Italy',      44.104,     9.823,  'Sea', 'Import / Export'],
            ['Port of Livorno',      'ITLIO', 'Italy',      43.548,    10.311,  'Sea', 'Import / Export'],
            ['Port of Trieste',      'ITTRS', 'Italy',      45.643,    13.762,  'Sea', 'Import / Export'],
            ['Port of Venice',       'ITVCE', 'Italy',      45.438,    12.332,  'Sea', 'Cargo'],
            ['Port of Naples',       'ITNAP', 'Italy',      40.838,    14.251,  'Sea', 'Import / Export'],
            ['Port of Taranto',      'ITTTR', 'Italy',      40.464,    17.248,  'Sea', 'Cargo'],
            ['Port of Palermo',      'ITPMO', 'Italy',      38.132,    13.367,  'Sea', 'Cargo'],
            ['Port of Civitavecchia','ITCIV', 'Italy',      42.091,    11.796,  'Sea', 'Cargo'],

            // =============================
            // USA (20 ports)
            // =============================
            ['Port of Los Angeles',  'USLAX', 'United States', 33.741, -118.272, 'Sea', 'Import / Export'],
            ['Port of Long Beach',   'USLGB', 'United States', 33.770, -118.194, 'Sea', 'Import / Export'],
            ['Port of New York',     'USNYC', 'United States', 40.713,  -74.006, 'Sea', 'Import / Export'],
            ['Port of Houston',      'USHOU', 'United States', 29.760,  -95.370, 'Sea', 'Import / Export'],
            ['Port of Savannah',     'USSAV', 'United States', 32.081,  -81.100, 'Sea', 'Import / Export'],
            ['Port of Seattle',      'USSEA', 'United States', 47.608, -122.335, 'Sea', 'Import / Export'],
            ['Port of Tacoma',       'USTAC', 'United States', 47.254, -122.444, 'Sea', 'Import / Export'],
            ['Port of New Orleans',  'USMSY', 'United States', 29.954,  -90.075, 'Sea', 'Import / Export'],
            ['Port of Baltimore',    'USBLT', 'United States', 39.286,  -76.612, 'Sea', 'Import / Export'],
            ['Port of Norfolk',      'USORF', 'United States', 36.847,  -76.300, 'Sea', 'Import / Export'],
            ['Port of Charleston',   'USCHS', 'United States', 32.776,  -79.931, 'Sea', 'Import / Export'],
            ['Port of Miami',        'USMIA', 'United States', 25.774,  -80.186, 'Sea', 'Import / Export'],
            ['Port of Tampa',        'USTPA', 'United States', 27.950,  -82.457, 'Sea', 'Cargo'],
            ['Port of Oakland',      'USOAK', 'United States', 37.805, -122.272, 'Sea', 'Import / Export'],
            ['Port of Portland',     'USPDX', 'United States', 45.523, -122.676, 'Sea', 'Cargo'],
            ['Port of Anchorage',    'USANC', 'United States', 61.218, -149.900, 'Sea', 'Cargo'],
            ['Port of Philadelphia', 'USPHL', 'United States', 39.953,  -75.164, 'Sea', 'Cargo'],
            ['Port of Boston',       'USBOS', 'United States', 42.360,  -71.057, 'Sea', 'Cargo'],
            ['Port of Jacksonville', 'USJAX', 'United States', 30.332,  -81.656, 'Sea', 'Cargo'],
            ['Port of Corpus Christi','USCRP','United States', 27.800,  -97.396, 'Sea', 'Cargo'],

            // =============================
            // CANADA (8 ports)
            // =============================
            ['Port of Vancouver',    'CAVAN', 'Canada',     49.283,  -123.121,  'Sea', 'Import / Export'],
            ['Port of Montreal',     'CAMTR', 'Canada',     45.508,   -73.554,  'Sea', 'Import / Export'],
            ['Port of Halifax',      'CAHAL', 'Canada',     44.649,   -63.599,  'Sea', 'Import / Export'],
            ['Port of Prince Rupert','CAPRU', 'Canada',     54.316,  -130.329,  'Sea', 'Import / Export'],
            ['Port of Saint John',   'CASTJ', 'Canada',     45.274,   -66.062,  'Sea', 'Cargo'],
            ['Port of Toronto',      'CATOR', 'Canada',     43.641,   -79.387,  'Sea', 'Cargo'],
            ['Port of Quebec',       'CAQUE', 'Canada',     46.810,   -71.208,  'Sea', 'Cargo'],
            ['Port of Churchill',    'CACHH', 'Canada',     58.767,   -94.170,  'Sea', 'Cargo'],

            // =============================
            // BRAZIL (10 ports)
            // =============================
            ['Port of Santos',       'BRSSZ', 'Brazil',    -23.961,   -46.334,  'Sea', 'Import / Export'],
            ['Port of Rio de Janeiro','BRRIO', 'Brazil',   -22.910,   -43.172,  'Sea', 'Import / Export'],
            ['Port of Paranaguá',    'BRPNG', 'Brazil',    -25.521,   -48.511,  'Sea', 'Import / Export'],
            ['Port of Itaguaí',      'BRITG', 'Brazil',    -22.858,   -43.800,  'Sea', 'Cargo'],
            ['Port of Suape',        'BRSUP', 'Brazil',     -8.394,   -34.974,  'Sea', 'Cargo'],
            ['Port of Fortaleza',    'BRFOR', 'Brazil',     -3.717,   -38.542,  'Sea', 'Cargo'],
            ['Port of Salvador',     'BRSVI', 'Brazil',    -12.974,   -38.501,  'Sea', 'Cargo'],
            ['Port of Belém',        'BRBEL', 'Brazil',     -1.455,   -48.503,  'Sea', 'Cargo'],
            ['Port of Itajaí',       'BRITJ', 'Brazil',    -26.909,   -48.661,  'Sea', 'Import / Export'],
            ['Port of Manaus',       'BRMAO', 'Brazil',     -3.119,   -60.022, 'River','Cargo'],

            // =============================
            // UAE (4 ports)
            // =============================
            ['Jebel Ali',            'AEJEA', 'United Arab Emirates',  25.011,  55.062, 'Sea', 'Import / Export'],
            ['Port of Dubai',        'AEDXB', 'United Arab Emirates',  25.267,  55.299, 'Sea', 'Import / Export'],
            ['Port of Abu Dhabi',    'AEAUH', 'United Arab Emirates',  24.467,  54.367, 'Sea', 'Cargo'],
            ['Port of Sharjah',      'AESHJ', 'United Arab Emirates',  25.365,  55.428, 'Sea', 'Cargo'],

            // =============================
            // SAUDI ARABIA (5 ports)
            // =============================
            ['Jeddah Islamic Port',  'SAJED', 'Saudi Arabia', 21.486,  39.193,  'Sea', 'Import / Export'],
            ['King Abdulaziz Port',  'SADMM', 'Saudi Arabia', 26.430,  50.103,  'Sea', 'Import / Export'],
            ['Jubail Industrial',    'SAJUB', 'Saudi Arabia', 27.002,  49.658,  'Sea', 'Cargo'],
            ['Dammam Port',          'SADMM', 'Saudi Arabia', 26.430,  50.100,  'Sea', 'Cargo'],
            ['Yanbu Port',           'SAYNB', 'Saudi Arabia', 24.089,  38.064,  'Sea', 'Cargo'],

            // =============================
            // SOUTH AFRICA (8 ports)
            // =============================
            ['Port of Durban',       'ZADUR', 'South Africa', -29.859,  31.022, 'Sea', 'Import / Export'],
            ['Port of Cape Town',    'ZACPT', 'South Africa', -33.904,  18.422, 'Sea', 'Import / Export'],
            ['Port of Port Elizabeth','ZAPLZ', 'South Africa',-33.958,  25.596, 'Sea', 'Import / Export'],
            ['Port of East London',  'ZAELS', 'South Africa', -33.017,  27.906, 'Sea', 'Cargo'],
            ['Port of Richards Bay', 'ZARCB', 'South Africa', -28.798,  32.068, 'Sea', 'Cargo'],
            ['Port of Saldanha Bay', 'ZASDB', 'South Africa', -33.011,  17.935, 'Sea', 'Cargo'],
            ['Port of Mossel Bay',   'ZAMOB', 'South Africa', -34.183,  22.150, 'Sea', 'Cargo'],
            ['Port of Ngqura',       'ZANGQ', 'South Africa', -33.840,  25.800, 'Sea', 'Cargo'],

            // =============================
            // EGYPT (4 ports)
            // =============================
            ['Port Said',            'EGPSD', 'Egypt',       31.259,   32.286,  'Sea', 'Import / Export'],
            ['Port of Alexandria',   'EGALX', 'Egypt',       31.200,   29.887,  'Sea', 'Import / Export'],
            ['Damietta Port',        'EGDAM', 'Egypt',       31.416,   31.817,  'Sea', 'Cargo'],
            ['Ain Sokhna Port',      'EGAIS', 'Egypt',       29.567,   32.350,  'Sea', 'Cargo'],

            // =============================
            // TURKEY (6 ports)
            // =============================
            ['Port of Mersin',       'TRMER', 'Turkey',      36.794,   34.631,  'Sea', 'Import / Export'],
            ['Port of Ambarlı',      'TRAMB', 'Turkey',      40.991,   28.699,  'Sea', 'Import / Export'],
            ['Port of Izmir',        'TRIZM', 'Turkey',      38.418,   27.150,  'Sea', 'Import / Export'],
            ['Port of Istanbul',     'TRIST', 'Turkey',      41.007,   28.981,  'Sea', 'Cargo'],
            ['Port of Gemlik',       'TRGEM', 'Turkey',      40.429,   29.148,  'Sea', 'Cargo'],
            ['Port of Trabzon',      'TRTBS', 'Turkey',      41.003,   39.731,  'Sea', 'Cargo'],

            // =============================
            // RUSSIA (8 ports)
            // =============================
            ['Port of Novorossiysk', 'RUNVS', 'Russia',      44.723,   37.770,  'Sea', 'Import / Export'],
            ['Port of St Petersburg','RULEV', 'Russia',      59.920,   30.318,  'Sea', 'Import / Export'],
            ['Port of Vladivostok',  'RUVVO', 'Russia',      43.115,   131.879, 'Sea', 'Import / Export'],
            ['Port of Murmansk',     'RUMRM', 'Russia',      68.958,   33.093,  'Sea', 'Cargo'],
            ['Port of Nakhodka',     'RUNAK', 'Russia',      42.815,   132.886, 'Sea', 'Cargo'],
            ['Port of Vostochny',    'RUVOS', 'Russia',      42.760,   133.090, 'Sea', 'Cargo'],
            ['Port of Kaliningrad',  'RUKAN', 'Russia',      54.707,   20.510,  'Sea', 'Cargo'],
            ['Port of Sakhalin',     'RUYUZ', 'Russia',      46.959,   142.738, 'Sea', 'Cargo'],

            // =============================
            // UNITED KINGDOM (8 ports)
            // =============================
            ['Port of Felixstowe',   'GBFXT', 'United Kingdom', 51.962,  1.352, 'Sea', 'Import / Export'],
            ['Port of Southampton',  'GBSOU', 'United Kingdom', 50.897, -1.399, 'Sea', 'Import / Export'],
            ['Port of London (Tilbury)','GBTIN','United Kingdom',51.455,  0.356, 'Sea', 'Import / Export'],
            ['Port of Liverpool',    'GBLIV', 'United Kingdom', 53.400, -2.993, 'Sea', 'Import / Export'],
            ['Port of Grimsby',      'GBGRI', 'United Kingdom', 53.575, -0.083, 'Sea', 'Cargo'],
            ['Port of Hull',         'GBHUL', 'United Kingdom', 53.745, -0.336, 'Sea', 'Cargo'],
            ['Port of Bristol',      'GBBRS', 'United Kingdom', 51.449, -2.602, 'Sea', 'Cargo'],
            ['Port of Glasgow',      'GBGLW', 'United Kingdom', 55.864, -4.252, 'Sea', 'Cargo'],

            // =============================
            // GREECE (5 ports)
            // =============================
            ['Port of Piraeus',      'GRPIR', 'Greece',      37.944,   23.638,  'Sea', 'Import / Export'],
            ['Port of Thessaloniki', 'GRTHK', 'Greece',      40.641,   22.944,  'Sea', 'Import / Export'],
            ['Port of Patras',       'GRPAT', 'Greece',      38.247,   21.735,  'Sea', 'Cargo'],
            ['Port of Heraklion',    'GRHKI', 'Greece',      35.340,   25.134,  'Sea', 'Cargo'],
            ['Port of Volos',        'GRVOL', 'Greece',      39.362,   22.942,  'Sea', 'Cargo'],

            // =============================
            // PHILIPPINES (6 ports)
            // =============================
            ['Port of Manila',       'PHPNK', 'Philippines', 14.591,  120.981,  'Sea', 'Import / Export'],
            ['Port of Cebu',         'PHCEB', 'Philippines', 10.294,  123.900,  'Sea', 'Import / Export'],
            ['Port of Davao',        'PHDAV', 'Philippines',  7.073,  125.612,  'Sea', 'Import / Export'],
            ['Port of Cagayan de Oro','PHCGY','Philippines',   8.480,  124.652,  'Sea', 'Cargo'],
            ['Port of Subic Bay',    'PHSFS', 'Philippines', 14.794,  120.281,  'Sea', 'Cargo'],
            ['Port of Batangas',     'PHBTG', 'Philippines', 13.763,  121.079,  'Sea', 'Cargo'],

            // =============================
            // THAILAND (5 ports)
            // =============================
            ['Laem Chabang',         'THLCH', 'Thailand',    13.087,  100.879,  'Sea', 'Import / Export'],
            ['Bangkok Port',         'THBKK', 'Thailand',    13.736,  100.516,  'Sea', 'Import / Export'],
            ['Map Ta Phut',          'THMTP', 'Thailand',    12.670,  101.167,  'Sea', 'Cargo'],
            ['Songkhla Port',        'THSGZ', 'Thailand',     7.200,  100.600,  'Sea', 'Cargo'],
            ['Ranong Port',          'THRNG', 'Thailand',     9.967,   98.633,  'Sea', 'Cargo'],

            // =============================
            // VIETNAM (8 ports)
            // =============================
            ['Cat Lai Port',         'VNSGN', 'Vietnam',     10.751,  106.754,  'Sea', 'Import / Export'],
            ['Hai Phong Port',       'VNHPH', 'Vietnam',     20.866,  106.688,  'Sea', 'Import / Export'],
            ['Cai Mep Port',         'VNCAM', 'Vietnam',     10.563,  107.001,  'Sea', 'Import / Export'],
            ['Da Nang Port',         'VNDAD', 'Vietnam',     16.068,  108.217,  'Sea', 'Import / Export'],
            ['Quy Nhon Port',        'VNUIH', 'Vietnam',     13.776,  109.229,  'Sea', 'Cargo'],
            ['Nha Trang Port',       'VNNHA', 'Vietnam',     12.237,  109.196,  'Sea', 'Cargo'],
            ['Can Tho Port',         'VNVCA', 'Vietnam',     10.046,  105.788, 'River','Cargo'],
            ['Vung Tau Port',        'VNVUT', 'Vietnam',     10.346,  107.084,  'Sea', 'Cargo'],

            // =============================
            // TAIWAN (4 ports)
            // =============================
            ['Port of Kaohsiung',    'TWKHH', 'Taiwan',      22.620,  120.272,  'Sea', 'Import / Export'],
            ['Port of Taichung',     'TWTXG', 'Taiwan',      24.285,  120.534,  'Sea', 'Import / Export'],
            ['Port of Keelung',      'TWKEL', 'Taiwan',      25.127,  121.740,  'Sea', 'Import / Export'],
            ['Port of Hualien',      'TWHUA', 'Taiwan',      23.973,  121.617,  'Sea', 'Cargo'],

            // =============================
            // PAKISTAN (3 ports)
            // =============================
            ['Port Qasim',           'PKBQM', 'Pakistan',    24.800,   67.350,  'Sea', 'Import / Export'],
            ['Karachi Port',         'PKKHI', 'Pakistan',    24.840,   66.990,  'Sea', 'Import / Export'],
            ['Gwadar Port',          'PKGWD', 'Pakistan',    25.130,   62.330,  'Sea', 'Cargo'],

            // =============================
            // SRI LANKA (2 ports)
            // =============================
            ['Port of Colombo',      'LKCMB', 'Sri Lanka',    6.950,   79.850,  'Sea', 'Import / Export'],
            ['Port of Hambantota',   'LKHBA', 'Sri Lanka',    6.124,   81.107,  'Sea', 'Cargo'],

            // =============================
            // MYANMAR (2 ports)
            // =============================
            ['Port of Yangon',       'MMRGN', 'Myanmar',     16.780,   96.150,  'Sea', 'Import / Export'],
            ['Thilawa Port',         'MMTHW', 'Myanmar',     16.580,   96.280,  'Sea', 'Cargo'],

            // =============================
            // BANGLADESH (2 ports)
            // =============================
            ['Port of Chittagong',   'BDCGP', 'Bangladesh',  22.330,   91.830,  'Sea', 'Import / Export'],
            ['Mongla Port',          'BDMGL', 'Bangladesh',  22.487,   89.587,  'Sea', 'Cargo'],

            // =============================
            // CAMBODIA (1 port)
            // =============================
            ['Port of Sihanoukville','KHKOS', 'Cambodia',    10.626,  103.523,  'Sea', 'Import / Export'],

            // =============================
            // NIGERIA (4 ports)
            // =============================
            ['Port of Lagos (Apapa)','NGAPP', 'Nigeria',      6.449,    3.383,  'Sea', 'Import / Export'],
            ['Port of Tin Can Island','NGTCI', 'Nigeria',      6.437,    3.340,  'Sea', 'Import / Export'],
            ['Port of Warri',        'NGWRR', 'Nigeria',      5.517,    5.750,  'Sea', 'Cargo'],
            ['Port Harcourt',        'NGPHC', 'Nigeria',      4.779,    7.025,  'Sea', 'Cargo'],

            // =============================
            // KENYA (1 port)
            // =============================
            ['Port of Mombasa',      'KEMBA', 'Kenya',       -4.043,   39.668,  'Sea', 'Import / Export'],

            // =============================
            // TANZANIA (1 port)
            // =============================
            ['Port of Dar es Salaam','TZDAR', 'Tanzania',    -6.817,   39.292,  'Sea', 'Import / Export'],

            // =============================
            // ETHIOPIA (1 port)
            // =============================
            ['Port of Djibouti',     'DJJIB', 'Djibouti',   11.604,   43.148,  'Sea', 'Import / Export'],

            // =============================
            // GHANA (1 port)
            // =============================
            ['Tema Port',            'GHTEM', 'Ghana',        5.633,    0.017,  'Sea', 'Import / Export'],

            // =============================
            // MOROCCO (3 ports)
            // =============================
            ['Tanger Med',           'MATNG', 'Morocco',     35.897,   -5.500,  'Sea', 'Import / Export'],
            ['Port of Casablanca',   'MACAS', 'Morocco',     33.600,   -7.616,  'Sea', 'Import / Export'],
            ['Port of Agadir',       'MAAGA', 'Morocco',     30.421,   -9.636,  'Sea', 'Cargo'],

            // =============================
            // MOZAMBIQUE (1 port)
            // =============================
            ['Port of Maputo',       'MZMPM', 'Mozambique',  -25.960,  32.570,  'Sea', 'Cargo'],

            // =============================
            // ARGENTINA (4 ports)
            // =============================
            ['Port of Buenos Aires', 'ARBUE', 'Argentina',   -34.607,  -58.437, 'Sea', 'Import / Export'],
            ['Port of Rosario',      'ARROS', 'Argentina',   -32.950,  -60.650, 'River','Cargo'],
            ['Port of Bahia Blanca', 'ARBHI', 'Argentina',   -38.719,  -62.272, 'Sea', 'Cargo'],
            ['Port of Mar del Plata','ARMQP', 'Argentina',   -38.003,  -57.534, 'Sea', 'Cargo'],

            // =============================
            // CHILE (4 ports)
            // =============================
            ['Port of San Antonio',  'CLSAI', 'Chile',       -33.591,  -71.619, 'Sea', 'Import / Export'],
            ['Port of Valparaíso',   'CLVAP', 'Chile',       -33.047,  -71.622, 'Sea', 'Import / Export'],
            ['Port of Iquique',      'CLIQQ', 'Chile',       -20.217,  -70.167, 'Sea', 'Cargo'],
            ['Port of Antofagasta',  'CLANT', 'Chile',       -23.647,  -70.400, 'Sea', 'Cargo'],

            // =============================
            // COLOMBIA (3 ports)
            // =============================
            ['Port of Buenaventura', 'COBUN', 'Colombia',     3.879,  -77.066,  'Sea', 'Import / Export'],
            ['Port of Cartagena',    'COCRT', 'Colombia',    10.400,  -75.514,  'Sea', 'Import / Export'],
            ['Port of Barranquilla', 'COBAQ', 'Colombia',    10.963,  -74.806,  'Sea', 'Cargo'],

            // =============================
            // PERU (3 ports)
            // =============================
            ['Port of Callao',       'PECLL', 'Peru',        -12.046,  -77.134, 'Sea', 'Import / Export'],
            ['Port of Paita',        'PEPAI', 'Peru',         -5.083,  -81.117, 'Sea', 'Cargo'],
            ['Port of Matarani',     'PEMAT', 'Peru',        -16.998,  -72.108, 'Sea', 'Cargo'],

            // =============================
            // MEXICO (5 ports)
            // =============================
            ['Port of Manzanillo',   'MXMAN', 'Mexico',      19.050, -104.317,  'Sea', 'Import / Export'],
            ['Port of Lázaro Cárdenas','MXLZC','Mexico',     17.932, -102.199,  'Sea', 'Import / Export'],
            ['Port of Veracruz',     'MXVER', 'Mexico',      19.201,  -96.134,  'Sea', 'Import / Export'],
            ['Port of Altamira',     'MXATM', 'Mexico',      22.413,  -97.905,  'Sea', 'Cargo'],
            ['Port of Ensenada',     'MXESE', 'Mexico',      31.867, -116.596,  'Sea', 'Cargo'],

            // =============================
            // PORTUGAL (3 ports)
            // =============================
            ['Port of Sines',        'PTSIN', 'Portugal',    37.956,   -8.868,  'Sea', 'Import / Export'],
            ['Port of Lisbon',       'PTLIS', 'Portugal',    38.714,   -9.139,  'Sea', 'Import / Export'],
            ['Port of Leixões',      'PTLEI', 'Portugal',    41.188,   -8.703,  'Sea', 'Import / Export'],

            // =============================
            // SWEDEN (3 ports)
            // =============================
            ['Port of Gothenburg',   'SEGOT', 'Sweden',      57.709,   11.973,  'Sea', 'Import / Export'],
            ['Port of Stockholm',    'SESTO', 'Sweden',      59.331,   18.063,  'Sea', 'Cargo'],
            ['Port of Helsingborg',  'SEHEL', 'Sweden',      56.046,   12.695,  'Sea', 'Cargo'],

            // =============================
            // NORWAY (3 ports)
            // =============================
            ['Port of Bergen',       'NOBGO', 'Norway',      60.391,    5.324,  'Sea', 'Import / Export'],
            ['Port of Oslo',         'NOOSL', 'Norway',      59.911,   10.740,  'Sea', 'Import / Export'],
            ['Port of Stavanger',    'NOSVG', 'Norway',      58.970,    5.731,  'Sea', 'Cargo'],

            // =============================
            // DENMARK (3 ports)
            // =============================
            ['Port of Aarhus',       'DKAAR', 'Denmark',     56.156,   10.213,  'Sea', 'Import / Export'],
            ['Port of Copenhagen',   'DKCPH', 'Denmark',     55.676,   12.568,  'Sea', 'Cargo'],
            ['Port of Fredericia',   'DKFRC', 'Denmark',     55.564,    9.748,  'Sea', 'Cargo'],

            // =============================
            // FINLAND (2 ports)
            // =============================
            ['Port of Helsinki',     'FIHEL', 'Finland',     60.162,   24.933,  'Sea', 'Import / Export'],
            ['Port of Kotka',        'FIKOT', 'Finland',     60.466,   26.947,  'Sea', 'Import / Export'],

            // =============================
            // POLAND (3 ports)
            // =============================
            ['Port of Gdańsk',       'PLGDN', 'Poland',      54.357,   18.643,  'Sea', 'Import / Export'],
            ['Port of Gdynia',       'PLGDY', 'Poland',      54.521,   18.532,  'Sea', 'Import / Export'],
            ['Port of Szczecin',     'PLSZZ', 'Poland',      53.432,   14.550,  'Sea', 'Cargo'],

            // =============================
            // IRAN (3 ports)
            // =============================
            ['Shahid Rajaee Port',   'IRBND', 'Iran',        27.117,   56.083,  'Sea', 'Import / Export'],
            ['Port of Bandar Abbas', 'IRTHK', 'Iran',        27.183,   56.250,  'Sea', 'Import / Export'],
            ['Port of Imam Khomeini','IRIMK', 'Iran',        30.451,   49.095,  'Sea', 'Cargo'],

            // =============================
            // ISRAEL (2 ports)
            // =============================
            ['Port of Haifa',        'ILHFA', 'Israel',      32.820,   34.996,  'Sea', 'Import / Export'],
            ['Port of Ashdod',       'ILASH', 'Israel',      31.820,   34.645,  'Sea', 'Import / Export'],

            // =============================
            // NEW ZEALAND (3 ports)
            // =============================
            ['Port of Auckland',     'NZAKL', 'New Zealand', -36.840,  174.764, 'Sea', 'Import / Export'],
            ['Port of Tauranga',     'NZTRG', 'New Zealand', -37.688,  176.168, 'Sea', 'Import / Export'],
            ['Port of Wellington',   'NZWLG', 'New Zealand', -41.286,  174.776, 'Sea', 'Cargo'],

            // =============================
            // HONG KONG (1 port)
            // =============================
            ['Port of Hong Kong',    'HKHKG', 'Hong Kong',   22.302,   114.177, 'Sea', 'Import / Export'],

            // =============================
            // UKRAINE (2 ports)
            // =============================
            ['Port of Odessa',       'UAODS', 'Ukraine',     46.475,   30.758,  'Sea', 'Import / Export'],
            ['Port of Chornomorsk',  'UAILL', 'Ukraine',     46.298,   30.658,  'Sea', 'Cargo'],

            // =============================
            // PANAMA (2 ports)
            // =============================
            ['Balboa Port',          'PAPAC', 'Panama',       8.964,  -79.571,  'Sea', 'Import / Export'],
            ['Colón Port',           'PACON', 'Panama',       9.360,  -79.900,  'Sea', 'Import / Export'],

            // =============================
            // CUBA (1 port)
            // =============================
            ['Port of Havana',       'CUHAV', 'Cuba',        23.137,  -82.361,  'Sea', 'Import / Export'],

            // =============================
            // ECUADOR (2 ports)
            // =============================
            ['Port of Guayaquil',    'ECGYE', 'Ecuador',     -2.186,  -79.900,  'Sea', 'Import / Export'],
            ['Port of Manta',        'ECMEC', 'Ecuador',     -0.954,  -80.715,  'Sea', 'Cargo'],

        ];

        foreach ($ports as $port) {
            $countryName = $port[2];
            $country = Country::where('name', $countryName)->first();

            if (!$country) {
                // Try partial match
                $country = Country::where('name', 'like', "%{$countryName}%")->first();
            }

            if (!$country) {
                continue;
            }

            Port::updateOrCreate(
                ['locode' => $port[1]],
                [
                    'name'       => $port[0],
                    'locode'     => $port[1],
                    'country_id' => $country->id,
                    'latitude'   => $port[3],
                    'longitude'  => $port[4],
                    'port_type'  => $port[5] ?? 'Sea',
                    'status'     => 'Active',
                    'function'   => $port[6] ?? 'Import / Export',
                    'outflows'   => rand(50000, 900000),
                ]
            );
        }
    }
}