<?php

return [
    'menu' => [
    'units' => 'Unidades',
    'inventory' => 'Inventario',
    'inventoryItems' => 'Ítems de Inventario',
    'dashboard' => 'Panel de Control',
    'inventoryStocks' => 'Existencias de Inventario',
    'inventoryMovements' => 'Movimientos de Inventario',
    'recipes' => 'Recetas',
    'purchaseOrders' => 'Órdenes de Compra',
    'reports' => 'Reportes',
    'settings' => 'Configuraciones',
    'purchaseOrderSettings' => 'Configuración de Órdenes de Compra',
    'suppliers' => 'Proveedores',
    'inventoryItemCategories' => 'Categorías de Ítems de Inventario',
],
'supplier' => [
    'suppliers' => 'Proveedores',
    'suppliersDescription' => 'Gestiona los proveedores de tu restaurante',
    'addSupplier' => 'Agregar Proveedor',
    'editSupplier' => 'Editar Proveedor',
    'deleteSupplier' => 'Eliminar Proveedor',
    'deleteSupplierMessage' => '¿Estás seguro de que deseas eliminar este proveedor? Esta acción no se puede deshacer.',
    'supplierDeleted' => 'Proveedor eliminado con éxito',
    'noSuppliersFound' => 'No se encontraron proveedores',
    'searchPlaceholder' => 'Buscar proveedores por nombre, correo o teléfono...',
    'name' => 'Nombre',
    'email' => 'Correo Electrónico',
    'phone' => 'Teléfono',
    'address' => 'Dirección',
    'supplierUpdated' => 'Proveedor actualizado con éxito',
    'supplierAdded' => 'Proveedor agregado con éxito',
    'supplierHasOrders' => 'Este proveedor tiene :count órdenes de compra, no se puede eliminar.',
    'supplierInformation' => 'Información del Proveedor',
],
'unit' => [
    'addUnit' => 'Agregar Unidad',
    'unitName' => 'Nombre de la Unidad',
    'unitSymbol' => 'Símbolo de la Unidad',
    'editUnit' => 'Editar Unidad',
    'noUnitFound' => 'No se encontraron unidades',
    'unitAdded' => 'Unidad agregada con éxito',
    'unitUpdated' => 'Unidad actualizada con éxito',
    'deleteUnit' => 'Eliminar Unidad',
    'deleteUnitMessage' => '¿Estás seguro de que deseas eliminar esta unidad?',
    'unitDeleted' => 'Unidad eliminada con éxito',
],
'inventoryItem' => [
    'addInventoryItem' => 'Agregar Ítem de Inventario',
    'name' => 'Nombre del Ítem',
    'category' => 'Categoría',
    'unit' => 'Unidad',
    'thresholdQuantity' => 'Cantidad Mínima',
    'addNew' => 'Agregar Nuevo Ítem de Inventario',
    'basicInfo' => 'Información Básica',
    'thresholdSettings' => 'Configuraciones de Cantidad Mínima',
    'thresholdHelp' => 'Establece la cantidad mínima en la que deseas ser notificado para reabastecimiento.',
    'noInventoryItemFound' => 'No se encontraron ítems de inventario',
    'editInventoryItem' => 'Editar Ítem de Inventario',
    'deleteInventoryItem' => 'Eliminar Ítem de Inventario',
    'deleteInventoryItemMessage' => '¿Estás seguro de que deseas eliminar este ítem de inventario?',
    'inventoryItemDeleted' => 'Ítem de Inventario eliminado con éxito',
    'inventoryItemUpdated' => 'Ítem de Inventario actualizado con éxito',
    'inventoryItemAdded' => 'Ítem de Inventario agregado con éxito',
    'preferredSupplier' => 'Proveedor Preferido',
    'preferredSupplierHelp' => 'Requerido para crear una orden de compra cuando el stock esté por debajo del mínimo.',
    'reorderQuantity' => 'Cantidad de Reorden Automática',
    'reorderQuantityPlaceholder' => 'Ingrese la cantidad de reorden',
],
    'itemCategory' => [
        'addItemCategory' => 'Agregar Categoría de Ítem',
    'itemCategoryAdded' => 'Categoría de Ítem agregada con éxito',
    'itemCategoryName' => 'Nombre de la Categoría de Ítem',
    'editItemCategory' => 'Editar Categoría de Ítem',
    'deleteItemCategory' => 'Eliminar Categoría de Ítem',
    'deleteItemCategoryMessage' => '¿Estás seguro de que deseas eliminar esta categoría de ítem?',
    'itemCategoryDeleted' => 'Categoría de Ítem eliminada con éxito',
    'itemCategoryUpdated' => 'Categoría de Ítem actualizada con éxito',
    'noItemCategoryFound' => 'No se encontró ninguna categoría de ítem',
    ],
    'stock' => [
        'addStockEntry' => 'Agregar Entrada de Stock',
    'stockInventory' => 'Inventario de Stock',
    'stockInventoryDescription' => 'Gestiona y monitorea los ítems del inventario de tu restaurante',
    'addStockEntryDescription' => 'Ingresa los detalles para añadir nuevo stock al inventario',
    'transactionType' => 'Tipo de Transacción',
    'stockIn' => 'Entrada de Stock',
    'stockOut' => 'Salida de Stock',
    'waste' => 'Desperdicio',
    'transfer' => 'Transferencia',
    'selectItem' => 'Seleccionar Ítem',
    'quantity' => 'Cantidad',
    'selectSupplier' => 'Seleccionar Proveedor',
    'expiryDate' => 'Fecha de Vencimiento',
    'wasteReason' => 'Motivo del Desperdicio',
    'wasteReasonPlaceholder' => 'Ingresa el motivo del desperdicio',
    'selectBranch' => 'Seleccionar Sucursal',
    'stockEntryAddedSuccessfully' => 'Entrada de Stock agregada con éxito',
    'expiry' => 'Vencimiento',
    'spoilage' => 'Deterioro',
    'customerComplaint' => 'Reclamo del Cliente',
    'overPreparation' => 'Sobrepreparación',
    'other' => 'Otro',
    'availableItems' => 'Ítems Disponibles',
    'lowStockItems' => 'Ítems con Bajo Stock',
    'outOfStock' => 'Agotado',
    'searchPlaceholder' => 'Buscar ítems...',
    'allCategories' => 'Todas las Categorías',
    'allStatus' => 'Todos los Estados',
    'inStock' => 'En Stock',
    'lowStock' => 'Stock Bajo',
    'clearFilters' => 'Limpiar Filtros',
    'noStockItemsFound' => 'No se encontraron ítems en stock',
    'currentStock' => 'Stock Actual',
    'stockStatus' => 'Estado del Stock',
    'minStock' => 'Stock Mínimo',
    'actions' => 'Acciones',
    'updateStock' => 'Actualizar Stock',
    'needAttention' => 'ítems requieren atención',
    'needsImmediate' => 'Requiere acción inmediata',
    'searchItems' => 'Buscar ítems por nombre o categoría...',
    'noItemsFound' => 'No se encontraron ítems',
    'searchSupplier' => 'Buscar proveedor por nombre o teléfono...',
    'items' => 'ítems en la categoría',
    'below_threshold' => ':count ítems por debajo del mínimo',
    'out_of_stock' => ':count ítems agotados',
        'status' => [
            'adequate' => 'En Stock',
        'low-stock' => 'Requiere Atención',
        'out-of-stock' => 'Crítico'
        ],
        'unitPurchasePrice' => 'Precio de Compra por Unidad',
    'expirationDate' => 'Fecha de Expiración',
    'cost' => 'Costo',
    'totalCost' => 'Costo Total',
    ],
    'movements' => [
        'viewMovement' => 'Ver Movimiento',
    'editMovement' => 'Editar Movimiento',
    'title' => 'Movimientos del Inventario de Cocina',
    'add_stock' => 'Agregar Stock',
    'print_report' => 'Imprimir Reporte',
    'movementUpdatedSuccessfully' => 'Movimiento actualizado con éxito',
        // Stats
        'stock_in' => [
            'title' => 'Entrada de Stock',
        'subtitle' => 'Total de Entradas de Stock'
        ],
        'stock_out' => [
            'title' => 'Salida de Stock',
        'subtitle' => 'Total de Salidas de Stock'
        ],
        'waste' => [
            'title' => 'Desperdicio',
        'subtitle' => 'Total de Desperdicio de Stock'
        ],
        'transfers' => [
            'title' => 'Transferencias',
        'subtitle' => 'Total de Transferencias de Stock'
        ],

        // Filters
        'filters' => [
            'search_placeholder' => 'Buscar ingredientes, personal...',
'all_types' => 'Todos los Tipos',
'all_categories' => 'Todas las Categorías',
'clear_filters' => 'Limpiar Filtros',
            'types' => [
                'in' => 'Entrada de Stock',
'out' => 'Salida de Stock',
'waste' => 'Desperdicio',
'transfer' => 'Transferencia'
            ],
            'date_ranges' => [
                'today' => 'Hoy',
'week' => 'Esta Semana',
'month' => 'Este Mes',
'quarter' => 'Este Trimestre'
            ]
        ],

        // Table Headers
        'table' => [
            'date_time' => 'Fecha/Hora',
'item_category' => 'Ítem y Categoría',
'movement' => 'Movimiento',
'quantity_unit' => 'Cantidad',
'supplier' => 'Proveedor',
'staff' => 'Agregado Por',
'actions' => 'Acciones'
        ],

        // Loading State
        'loading' => 'Cargando...',

        // Empty State
        'no_movements' => 'No se encontraron movimientos de inventario',
'try_adjusting' => 'Intenta ajustar tu búsqueda o los criterios de filtrado',

        // Messages
        'edit_restriction_message' => 'Por motivos de auditoría, solo se pueden editar los movimientos de inventario de los últimos 7 días. Las transacciones más antiguas son solo de lectura.',
'edit_restriction_tooltip' => 'Solo se pueden editar los movimientos de los últimos 7 días <br> <small>Por motivos de auditoría</small>',

        'fields' => [
            'quantity' => 'Cantidad',
'date_time' => 'Fecha y Hora',
'added_by' => 'Agregado Por',
'supplier' => 'Proveedor',
'waste_reason' => 'Razón del Desperdicio',
'transfer_branch' => 'Sucursal de Destino',
'source_branch' => 'Sucursal de Origen',
        ],

        'types' => [
            'in' => 'Ingreso de Stock',
'out' => 'Salida de Stock',
'waste' => 'Desperdicio',
'transfer' => 'Transferencia',
        ],

        'waste_reasons' => [
            'expiry' => 'Caducidad',
'spoilage' => 'Descomposición',
'customer_complaint' => 'Queja del Cliente',
'over_preparation' => 'Sobrepreparación',
'other' => 'Otro',
        ],

        'select_supplier' => 'Seleccionar Proveedor',
'select_reason' => 'Seleccionar Motivo',
'select_branch' => 'Seleccionar Sucursal',
'update_movement' => 'Actualizar Movimiento',
    ],
    'recipe' => [
        'title' => 'Libro de Recetas',
'add_recipe' => 'Agregar Receta',
'export' => 'Exportar',
'search_placeholder' => 'Buscar recetas...',
'no_recipes_found' => 'No se encontraron recetas',
'get_started' => 'Comienza creando una nueva receta.',

        // Stats
        'stats' => [
            'total_recipes' => 'Total de Recetas',
'main_courses' => 'Platos Principales',
'avg_prep_time' => 'Tiempo Promedio de Preparación',
        ],

        // Filters
        'filters' => [
            'all_categories' => 'Todas las Categorías',
            'sort' => [
                'name' => 'Ordenar por Nombre',
'category' => 'Ordenar por Categoría',
'prep_time' => 'Ordenar por Tiempo de Preparación',
            ],
            'clear' => 'Borrar Filtros',
        ],

        // Recipe Details
        'ingredients_required' => 'Ingredientes Requeridos',
'preparation_time' => 'minutos',

'edit_recipe' => 'Editar Receta',
'menu_item' => 'Elemento del Menú',
'select_menu_item' => 'Seleccionar Elemento del Menú',
'ingredients' => 'Ingredientes',
'add_ingredient' => 'Añadir Ingrediente',
'ingredient' => 'Ingrediente',
'select_ingredient' => 'Seleccionar Ingrediente',
'quantity' => 'Cantidad',
'unit' => 'Unidad',
'select_unit' => 'Seleccionar Unidad',
'recipe_saved' => 'Receta guardada con éxito',
'recipe_deleted' => 'Receta eliminada con éxito',
'confirm_delete' => '¿Estás seguro de que deseas eliminar esta receta?',
'delete_recipe' => 'Eliminar Receta',
'ingredients_cost' => 'Costo de los Ingredientes',
    ],
    'purchaseOrder' => [
        'purchase_order' => 'Orden de Compra',
'created_by' => 'Creado Por',
'created_at' => 'Creado En',
// Títulos y Etiquetas
'create_title' => 'Crear Orden de Compra',
'edit_title' => 'Editar Orden de Compra',
'view_title' => 'Ver Orden de Compra',
'supplier' => 'Proveedor',
'select_supplier' => 'Seleccionar Proveedor',
'order_date' => 'Fecha de la Orden',
'expected_delivery_date' => 'Fecha de Entrega Esperada',
'items' => 'Artículos',
'add_item' => 'Añadir Artículo',
'select_item' => 'Seleccionar Artículo',
'quantity' => 'Cantidad',
'unit_price' => 'Precio Unitario',
'subtotal' => 'Subtotal',
'action' => 'Acción',
'remove' => 'Eliminar',
'notes' => 'Notas',
'save' => 'Guardar',
'cancel' => 'Cancelar',
'receive_title' => 'Recibir Orden de Compra',
'ordered_quantity' => 'Cantidad Ordenada',
'previously_received' => 'Recibido Anteriormente',
'receiving_quantity' => 'Cantidad Recibiendo',
'remaining' => 'Restante',
'received_quantity' => 'Cantidad Recibida',
'receive_items' => 'Recibir Artículos',

        // Messages
        'items_received' => 'Artículos recibidos correctamente.',
'cannot_receive' => 'Esta orden de compra no puede ser recibida.',
'invalid_quantity' => 'Cantidad de recepción inválida.',

// Estadísticas
'total_orders' => 'Total de Órdenes',
'pending_orders' => 'Órdenes Pendientes',
'completed_orders' => 'Órdenes Completadas',
'total_amount' => 'Monto Total',

        // Status
        'status' => [
            'draft' => 'Borrador',
'sent' => 'Enviado',
'received' => 'Recibido',
'partially_received' => 'Recibido Parcialmente',
'cancelled' => 'Cancelado'
        ],

        // Actions
        'mark_as_sent' => 'Marcar como Enviado',
'send' => 'Enviar',
'send_title' => 'Enviar Orden de Compra',
'send_confirm' => '¿Está seguro de que desea enviar esta orden de compra?',
'sent_successfully' => 'Orden de compra enviada con éxito',
'cancel' => 'Cancelar Orden',
'cancel_title' => 'Cancelar Orden de Compra',
'cancel_confirm' => '¿Está seguro de que desea cancelar esta orden de compra?',
'cancelled_successfully' => 'Orden de compra cancelada con éxito',
'delete_confirm' => '¿Está seguro de que desea eliminar esta orden de compra?',
'select_item_placeholder' => 'Seleccionar un artículo...',
'search_items' => 'Buscar artículos...',
'no_items_found' => 'No se encontraron artículos',
'type_to_search' => 'Escriba para buscar artículos...',
'loading_items' => 'Cargando artículos...',
'default_date' => 'Hoy',
'search_placeholder' => 'Buscar por número de OC o proveedor...',
'all_suppliers' => 'Todos los proveedores',
'all_status' => 'Todos los estados',
'select_date_range' => 'Seleccionar rango de fechas',
'clear_filters' => 'Limpiar filtros',
'po_number' => 'Número de OC',
'actions' => 'Acciones',
'edit' => 'Editar',
'receive' => 'Recibir',
'delete' => 'Eliminar',
'view' => 'Ver',
'download_pdf' => 'Descargar PDF',
'no_records' => 'No se encontraron órdenes de compra',
'delete_title' => 'Eliminar Orden de Compra',
'deleted_successfully' => 'Orden de compra eliminada con éxito',
'saved_successfully' => 'Orden de compra guardada con éxito',
'auto_purchase_order_notes' => 'Orden creada automáticamente por el sistema',
'view_all_purchase_orders' => 'Ver todas las órdenes de compra de este proveedor',
'no_purchase_orders' => 'No hay órdenes de compra',
'purchase_order_received' => 'Orden de Compra Recibida: :po_number',
    ],
    'dashboard' => [
        'title' => 'Panel de Inventario',
        'filters' => [
            'category' => 'Filtro de Categoría',
'all_categories' => 'Todas las Categorías',
'time_period' => 'Periodo de Tiempo',
            'periods' => [
                'daily' => 'Hoy',
'weekly' => 'Esta Semana',
'monthly' => 'Este Mes'
            ]
        ],
        'stock' => [
            'items' => 'artículos',
'below_threshold' => ':count artículos por debajo del umbral',
'out_of_stock' => ':count artículos fuera de stock',
            'status' => [
                'adequate' => 'En stock',
'low-stock' => 'Bajo stock',
'out-of-stock' => 'Fuera de stock',
            ]
        ],
        'sections' => [
            'top_moving' => [
                'title' => 'Artículos de Inventario de Mayor Movimiento',
'stock' => 'Stock',
'usage' => 'Uso',
'waste' => 'Desperdicio'
            ],
            'low_stock' => [
                'title' => 'Alertas de Bajo Stock',
'alerts' => ':count alertas',
'current' => 'Actual',
'threshold' => 'Umbral',
'no_items' => 'No hay artículos con bajo stock'
            ],
            'correlation' => [
                'title' => 'Correlación Uso-Stock',
'current_stock' => 'Stock Actual',
'usage' => 'Uso',
'stock_added' => 'Stock Agregado'
            ],
            'expiring_stock' => [
                'title' => 'Stock por Expirar',
'items' => 'artículos',
'expires_in' => 'Expira en :days días',
'stock' => 'Stock'
            ]
        ]
    ],
    'reports' => [
        'title' => 'Informes de Inventario',
        'tabs' => [
            'usage' => 'Tendencias de Uso',
'forecasting' => 'Pronóstico',
'turnover' => 'Tasa de Rotación',
'cogs' => 'Costo de los Bienes Vendidos'
        ],
        'filters' => [
            'period' => 'Período del Informe',
'start_date' => 'Fecha de Inicio',
'end_date' => 'Fecha de Finalización',
'search_items' => 'Buscar Artículos',
'search_placeholder' => 'Buscar por nombre...',
'select_item' => 'Seleccionar Artículo',
'all_items' => 'Todos los Artículos',
'forecast_period' => 'Período de Pronóstico',
            'periods' => [
                'daily' => 'Diario',
'weekly' => 'Semanal',
'monthly' => 'Mensual',
'week' => '7 días',
'fortnight' => '15 días',
'month' => '30 días',
'two_months' => '60 días',
'quarter' => '90 días'
            ],
        ],
        'usage' => [
            'title' => 'Análisis de Uso de Inventario',
'description' => 'Monitorea y analiza los patrones de uso del inventario para optimizar la gestión de existencias e identificar tendencias.',
'total_usage' => 'Uso Total',
'increase' => 'aumento',
'decrease' => 'disminución',
'trends_title' => 'Tendencias de Uso',
'item' => 'Artículo',
'quantity' => 'Cantidad',
'date' => 'Fecha',
'transaction_type' => 'Tipo de Transacción',
'historical_usage' => 'Uso Histórico',
'current_usage' => 'Uso Actual',
'per_day' => 'por día',
'export' => 'Exportar Reporte',
'current_period' => 'Período Actual',
'previous_period' => 'Período Anterior'
        ],
        'forecasting' => [
            'title' => 'Pronóstico de Uso de Inventario',
'description' => 'Predice las necesidades futuras de inventario basándose en patrones y tendencias de uso históricos.',
'historical_usage' => 'Uso Histórico',
'forecasted_usage' => 'Uso Pronosticado',
'current_stock' => 'Stock Actual',
'usage_count' => 'Cantidad de Uso',
'avg_daily_usage' => 'Uso Diario Promedio',
'estimated_days' => 'Días Estimados Restantes',
'per_day' => 'por día',
'days_left' => ':days días',
'item' => 'Artículo',
'forecast_analysis' => 'Análisis de Pronóstico',
'stock_prediction' => 'Predicción de Stock',
'transaction_count' => ':count transacción|:count transacciones',
        ],
        'turnover' => [
           'title' => 'Análisis de Rotación de Inventario',
'description' => 'Supervisa y analiza las tasas de rotación de inventario para optimizar los niveles de stock e identificar artículos de baja rotación.',
'turnover_rate' => 'Tasa de Rotación',
'current_stock' => 'Stock Actual',
'usage_count' => 'Cantidad de Uso',
'avg_turnover' => 'Rotación Promedio',
'top_items' => 'Artículos con Mayor Rotación',
'low_turnover' => 'Artículos de Baja Rotación',
'high_turnover' => 'Artículos de Alta Rotación',
'transaction_count' => ':count transacción|:count transacciones',
        ],
        'cogs' => [
            'title' => 'Costo de los Bienes Vendidos (COGS)',
'description' => 'Analiza el costo de los bienes vendidos para optimizar la gestión de inventario y mejorar la rentabilidad.',
'total_cost' => 'Costo Total',
'cost_per_unit' => 'Costo por Unidad',
'total_units' => 'Total de Unidades',
'total_cost_of_goods_sold' => 'Costo Total de los Bienes Vendidos',
            'filters' => [
                'start_date' => 'Fecha de Inicio',
'end_date' => 'Fecha de Fin',
'category' => 'Categoría',
'all_categories' => 'Todas las Categorías',
'generate_report' => 'Generar Informe'
            ],
            'table' => [
                'item' => 'Artículo',
'category' => 'Categoría',
'quantity_used' => 'Cantidad Usada',
'total_cost' => 'Costo Total'
            ],
            'summary' => [
                'total_cogs' => 'Costo Total de los Bienes Vendidos'
            ]
        ],
        'common' => [
            'no_data' => 'No hay datos disponibles para el período seleccionado',
'loading' => 'Cargando datos...',
'export_pdf' => 'Exportar PDF',
'export_excel' => 'Exportar Excel',
'print' => 'Imprimir informe',
'date_range' => 'Rango de fechas',
'custom_range' => 'Rango personalizado',
'apply' => 'Aplicar',
'reset' => 'Restablecer',
            'status' => [
                'critical' => 'Crítico',
'warning' => 'Advertencia',
'good' => 'Bueno'
            ]
        ],
        'stats' => [
            'total_items' => 'Total de Artículos',
'total_value' => 'Valor Total',
'avg_usage' => 'Uso Promedio',
'trend' => 'Tendencia',
            'comparison' => [
                'up' => 'Subió respecto al periodo anterior',
'down' => 'Bajó respecto al periodo anterior',
'same' => 'Igual que el periodo anterior'
            ]
        ],
        'chart_labels' => [
            'quantity' => 'Cantidad',
'value' => 'Valor',
'date' => 'Fecha',
'items' => 'Artículos',
'usage' => 'Uso',
'stock' => 'Inventario',
'forecast' => 'Pronóstico',
'trend' => 'Tendencia'
        ]
    ],
    'settings' => [
        'allowPurchaseOrder' => 'Permitir Orden de Compra automática',
'allowPurchaseOrderDescription' => 'Permitir que la orden de compra sea creada y enviada a los proveedores automáticamente.',
    ],
];
