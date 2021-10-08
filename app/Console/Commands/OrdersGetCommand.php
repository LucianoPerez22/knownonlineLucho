<?php

namespace App\Console\Commands;

use App\Http\Controllers\OrdersController;
use Illuminate\Console\Command;

class OrdersGetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:get {dateStart?} {dateEnd?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Obtiene las ordenes de mi tienda VTEX - Permite dos parametos dateStart
                              y dateEnd los mismos son opcionales. En caso de no setearlos
                              se traeran los ultimos 6 meses. Los mismos
                              deben ser seteados de la siguiente forma orders:get "2021-01-01" "2021-04-30"
                              es decir primero el año seguido de un - luego el mes seguido de un - y por
                              ultimo el dia todo encerrado entre comillas "". ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dateStart = $this->argument('dateStart');
        $dateEnd = $this->argument('dateEnd');

        $orders = new OrdersController();
        $orders->index($dateStart, $dateEnd);

        $this->info("Datos Recibidos y Almacenados correctamente 😀!!!");
    }
}
