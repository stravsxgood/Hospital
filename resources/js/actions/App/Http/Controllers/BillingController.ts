import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
    applyUrlDefaults,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\BillingController::index
 * @see app/Http/Controllers/BillingController.php:34
 * @route '/staff/billing'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/staff/billing',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\BillingController::index
 * @see app/Http/Controllers/BillingController.php:34
 * @route '/staff/billing'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\BillingController::index
 * @see app/Http/Controllers/BillingController.php:34
 * @route '/staff/billing'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\BillingController::index
 * @see app/Http/Controllers/BillingController.php:34
 * @route '/staff/billing'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\BillingController::index
 * @see app/Http/Controllers/BillingController.php:34
 * @route '/staff/billing'
 */
const indexForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\BillingController::index
 * @see app/Http/Controllers/BillingController.php:34
 * @route '/staff/billing'
 */
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\BillingController::index
 * @see app/Http/Controllers/BillingController.php:34
 * @route '/staff/billing'
 */
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

index.form = indexForm;
/**
 * @see \App\Http\Controllers\BillingController::show
 * @see app/Http/Controllers/BillingController.php:108
 * @route '/staff/billing/{id}'
 */
export const show = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});

show.definition = {
    methods: ['get', 'head'],
    url: '/staff/billing/{id}',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\BillingController::show
 * @see app/Http/Controllers/BillingController.php:108
 * @route '/staff/billing/{id}'
 */
show.url = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args };
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        id: args.id,
    };

    return (
        show.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\BillingController::show
 * @see app/Http/Controllers/BillingController.php:108
 * @route '/staff/billing/{id}'
 */
show.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\BillingController::show
 * @see app/Http/Controllers/BillingController.php:108
 * @route '/staff/billing/{id}'
 */
show.head = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\BillingController::show
 * @see app/Http/Controllers/BillingController.php:108
 * @route '/staff/billing/{id}'
 */
const showForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\BillingController::show
 * @see app/Http/Controllers/BillingController.php:108
 * @route '/staff/billing/{id}'
 */
showForm.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\BillingController::show
 * @see app/Http/Controllers/BillingController.php:108
 * @route '/staff/billing/{id}'
 */
showForm.head = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

show.form = showForm;
/**
 * @see \App\Http\Controllers\BillingController::createFromReservation
 * @see app/Http/Controllers/BillingController.php:137
 * @route '/staff/billing/create-from-reservation/{reservation_id}'
 */
export const createFromReservation = (
    args:
        | { reservation_id: string | number }
        | [reservation_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: createFromReservation.url(args, options),
    method: 'post',
});

createFromReservation.definition = {
    methods: ['post'],
    url: '/staff/billing/create-from-reservation/{reservation_id}',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\BillingController::createFromReservation
 * @see app/Http/Controllers/BillingController.php:137
 * @route '/staff/billing/create-from-reservation/{reservation_id}'
 */
createFromReservation.url = (
    args:
        | { reservation_id: string | number }
        | [reservation_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { reservation_id: args };
    }

    if (Array.isArray(args)) {
        args = {
            reservation_id: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        reservation_id: args.reservation_id,
    };

    return (
        createFromReservation.definition.url
            .replace('{reservation_id}', parsedArgs.reservation_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\BillingController::createFromReservation
 * @see app/Http/Controllers/BillingController.php:137
 * @route '/staff/billing/create-from-reservation/{reservation_id}'
 */
createFromReservation.post = (
    args:
        | { reservation_id: string | number }
        | [reservation_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: createFromReservation.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\BillingController::createFromReservation
 * @see app/Http/Controllers/BillingController.php:137
 * @route '/staff/billing/create-from-reservation/{reservation_id}'
 */
const createFromReservationForm = (
    args:
        | { reservation_id: string | number }
        | [reservation_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: createFromReservation.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\BillingController::createFromReservation
 * @see app/Http/Controllers/BillingController.php:137
 * @route '/staff/billing/create-from-reservation/{reservation_id}'
 */
createFromReservationForm.post = (
    args:
        | { reservation_id: string | number }
        | [reservation_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: createFromReservation.url(args, options),
    method: 'post',
});

createFromReservation.form = createFromReservationForm;
/**
 * @see \App\Http\Controllers\BillingController::payCash
 * @see app/Http/Controllers/BillingController.php:241
 * @route '/staff/billing/{id}/pay-cash'
 */
export const payCash = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: payCash.url(args, options),
    method: 'post',
});

payCash.definition = {
    methods: ['post'],
    url: '/staff/billing/{id}/pay-cash',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\BillingController::payCash
 * @see app/Http/Controllers/BillingController.php:241
 * @route '/staff/billing/{id}/pay-cash'
 */
payCash.url = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args };
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        id: args.id,
    };

    return (
        payCash.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\BillingController::payCash
 * @see app/Http/Controllers/BillingController.php:241
 * @route '/staff/billing/{id}/pay-cash'
 */
payCash.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: payCash.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\BillingController::payCash
 * @see app/Http/Controllers/BillingController.php:241
 * @route '/staff/billing/{id}/pay-cash'
 */
const payCashForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: payCash.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\BillingController::payCash
 * @see app/Http/Controllers/BillingController.php:241
 * @route '/staff/billing/{id}/pay-cash'
 */
payCashForm.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: payCash.url(args, options),
    method: 'post',
});

payCash.form = payCashForm;
/**
 * @see \App\Http\Controllers\BillingController::generateQris
 * @see app/Http/Controllers/BillingController.php:312
 * @route '/staff/billing/{id}/pay-qris'
 */
export const generateQris = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: generateQris.url(args, options),
    method: 'post',
});

generateQris.definition = {
    methods: ['post'],
    url: '/staff/billing/{id}/pay-qris',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\BillingController::generateQris
 * @see app/Http/Controllers/BillingController.php:312
 * @route '/staff/billing/{id}/pay-qris'
 */
generateQris.url = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args };
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        id: args.id,
    };

    return (
        generateQris.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\BillingController::generateQris
 * @see app/Http/Controllers/BillingController.php:312
 * @route '/staff/billing/{id}/pay-qris'
 */
generateQris.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: generateQris.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\BillingController::generateQris
 * @see app/Http/Controllers/BillingController.php:312
 * @route '/staff/billing/{id}/pay-qris'
 */
const generateQrisForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: generateQris.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\BillingController::generateQris
 * @see app/Http/Controllers/BillingController.php:312
 * @route '/staff/billing/{id}/pay-qris'
 */
generateQrisForm.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: generateQris.url(args, options),
    method: 'post',
});

generateQris.form = generateQrisForm;
/**
 * @see \App\Http\Controllers\BillingController::payEdc
 * @see app/Http/Controllers/BillingController.php:371
 * @route '/staff/billing/{id}/pay-edc'
 */
export const payEdc = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: payEdc.url(args, options),
    method: 'post',
});

payEdc.definition = {
    methods: ['post'],
    url: '/staff/billing/{id}/pay-edc',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\BillingController::payEdc
 * @see app/Http/Controllers/BillingController.php:371
 * @route '/staff/billing/{id}/pay-edc'
 */
payEdc.url = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args };
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        id: args.id,
    };

    return (
        payEdc.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\BillingController::payEdc
 * @see app/Http/Controllers/BillingController.php:371
 * @route '/staff/billing/{id}/pay-edc'
 */
payEdc.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: payEdc.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\BillingController::payEdc
 * @see app/Http/Controllers/BillingController.php:371
 * @route '/staff/billing/{id}/pay-edc'
 */
const payEdcForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: payEdc.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\BillingController::payEdc
 * @see app/Http/Controllers/BillingController.php:371
 * @route '/staff/billing/{id}/pay-edc'
 */
payEdcForm.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: payEdc.url(args, options),
    method: 'post',
});

payEdc.form = payEdcForm;
/**
 * @see \App\Http\Controllers\BillingController::payXendit
 * @see app/Http/Controllers/BillingController.php:438
 * @route '/staff/billing/{id}/pay-xendit'
 */
export const payXendit = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: payXendit.url(args, options),
    method: 'post',
});

payXendit.definition = {
    methods: ['post'],
    url: '/staff/billing/{id}/pay-xendit',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\BillingController::payXendit
 * @see app/Http/Controllers/BillingController.php:438
 * @route '/staff/billing/{id}/pay-xendit'
 */
payXendit.url = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args };
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        id: args.id,
    };

    return (
        payXendit.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\BillingController::payXendit
 * @see app/Http/Controllers/BillingController.php:438
 * @route '/staff/billing/{id}/pay-xendit'
 */
payXendit.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: payXendit.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\BillingController::payXendit
 * @see app/Http/Controllers/BillingController.php:438
 * @route '/staff/billing/{id}/pay-xendit'
 */
const payXenditForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: payXendit.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\BillingController::payXendit
 * @see app/Http/Controllers/BillingController.php:438
 * @route '/staff/billing/{id}/pay-xendit'
 */
payXenditForm.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: payXendit.url(args, options),
    method: 'post',
});

payXendit.form = payXenditForm;
/**
 * @see \App\Http\Controllers\BillingController::checkStatus
 * @see app/Http/Controllers/BillingController.php:350
 * @route '/staff/billing/{id}/status'
 */
export const checkStatus = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: checkStatus.url(args, options),
    method: 'get',
});

checkStatus.definition = {
    methods: ['get', 'head'],
    url: '/staff/billing/{id}/status',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\BillingController::checkStatus
 * @see app/Http/Controllers/BillingController.php:350
 * @route '/staff/billing/{id}/status'
 */
checkStatus.url = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args };
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        id: args.id,
    };

    return (
        checkStatus.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\BillingController::checkStatus
 * @see app/Http/Controllers/BillingController.php:350
 * @route '/staff/billing/{id}/status'
 */
checkStatus.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: checkStatus.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\BillingController::checkStatus
 * @see app/Http/Controllers/BillingController.php:350
 * @route '/staff/billing/{id}/status'
 */
checkStatus.head = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: checkStatus.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\BillingController::checkStatus
 * @see app/Http/Controllers/BillingController.php:350
 * @route '/staff/billing/{id}/status'
 */
const checkStatusForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: checkStatus.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\BillingController::checkStatus
 * @see app/Http/Controllers/BillingController.php:350
 * @route '/staff/billing/{id}/status'
 */
checkStatusForm.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: checkStatus.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\BillingController::checkStatus
 * @see app/Http/Controllers/BillingController.php:350
 * @route '/staff/billing/{id}/status'
 */
checkStatusForm.head = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: checkStatus.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

checkStatus.form = checkStatusForm;
/**
 * @see \App\Http\Controllers\BillingController::printThermalReceipt
 * @see app/Http/Controllers/BillingController.php:473
 * @route '/staff/billing/{id}/print-thermal'
 */
export const printThermalReceipt = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: printThermalReceipt.url(args, options),
    method: 'get',
});

printThermalReceipt.definition = {
    methods: ['get', 'head'],
    url: '/staff/billing/{id}/print-thermal',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\BillingController::printThermalReceipt
 * @see app/Http/Controllers/BillingController.php:473
 * @route '/staff/billing/{id}/print-thermal'
 */
printThermalReceipt.url = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args };
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        id: args.id,
    };

    return (
        printThermalReceipt.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\BillingController::printThermalReceipt
 * @see app/Http/Controllers/BillingController.php:473
 * @route '/staff/billing/{id}/print-thermal'
 */
printThermalReceipt.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: printThermalReceipt.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\BillingController::printThermalReceipt
 * @see app/Http/Controllers/BillingController.php:473
 * @route '/staff/billing/{id}/print-thermal'
 */
printThermalReceipt.head = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: printThermalReceipt.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\BillingController::printThermalReceipt
 * @see app/Http/Controllers/BillingController.php:473
 * @route '/staff/billing/{id}/print-thermal'
 */
const printThermalReceiptForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: printThermalReceipt.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\BillingController::printThermalReceipt
 * @see app/Http/Controllers/BillingController.php:473
 * @route '/staff/billing/{id}/print-thermal'
 */
printThermalReceiptForm.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: printThermalReceipt.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\BillingController::printThermalReceipt
 * @see app/Http/Controllers/BillingController.php:473
 * @route '/staff/billing/{id}/print-thermal'
 */
printThermalReceiptForm.head = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: printThermalReceipt.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

printThermalReceipt.form = printThermalReceiptForm;
const BillingController = {
    index,
    show,
    createFromReservation,
    payCash,
    generateQris,
    payEdc,
    payXendit,
    checkStatus,
    printThermalReceipt,
};

export default BillingController;
