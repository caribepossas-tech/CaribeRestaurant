<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-12">
        <div class="flex justify-center mb-10">
            <img src="{{ asset('img/logo.png') }}" class="h-16 hover:scale-110 transition-transform duration-300" alt="Home"/>
        </div>

        <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="bg-red-600 px-6 py-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-database text-white text-3xl"></i>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-bold text-white">Error en la conexión a la base de datos</h2>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-red-700 font-medium">No se puede establecer la conexión con la base de datos</p>
                            <p class="text-red-600 mt-1">La aplicación no pudo conectarse a la base de datos utilizando las credenciales proporcionadas.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-2">Posibles causas:</h4>
                        <ul class="list-disc list-inside text-gray-600 space-y-2">
                            <li>Credenciales de base de datos incorrectas en su archivo .env</li>
                            <li>El servidor de la base de datos no funciona</li>
                            <li>La base de datos no existe</li>
                            <li>Problemas de conectividad a la red</li>
                        </ul>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-medium text-blue-800 mb-2">Acciones recomendadas:</h4>
                        <ol class="list-decimal list-inside text-blue-600 space-y-2">
                            <li>Verifique sus credenciales de base de datos en el archivo .env</li>
                            <li>Compruebe si su servidor de base de datos está funcionando</li>
                            <li>Asegúrese de que la base de datos especificada existe</li>
                            <li>Póngase en contacto con el equipo de KOSARI.NET si el problema persiste</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>