import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\CashierShiftController::currentShift
 * @see app/Http/Controllers/CashierShiftController.php:30
 * @route '/staff/cashier-shifts/current'
 */
export const currentShift = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: currentShift.url(options),
    method: 'get',
})

currentShift.definition = {
    methods: ["get","head"],
    url: '/staff/cashier-shifts/current',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CashierShiftController::currentShift
 * @see app/Http/Controllers/CashierShiftController.php:30
 * @route '/staff/cashier-shifts/current'
 */
currentShift.url = (options?: RouteQueryOptions) => {
    return currentShift.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CashierShiftController::currentShift
 * @see app/Http/Controllers/CashierShiftController.php:30
 * @route '/staff/cashier-shifts/current'
 */
currentShift.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: currentShift.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CashierShiftController::currentShift
 * @see app/Http/Controllers/CashierShiftController.php:30
 * @route '/staff/cashier-shifts/current'
 */
currentShift.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: currentShift.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\CashierShiftController::currentShift
 * @see app/Http/Controllers/CashierShiftController.php:30
 * @route '/staff/cashier-shifts/current'
 */
    const currentShiftForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: currentShift.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\CashierShiftController::currentShift
 * @see app/Http/Controllers/CashierShiftController.php:30
 * @route '/staff/cashier-shifts/current'
 */
        currentShiftForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: currentShift.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\CashierShiftController::currentShift
 * @see app/Http/Controllers/CashierShiftController.php:30
 * @route '/staff/cashier-shifts/current'
 */
        currentShiftForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: currentShift.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    currentShift.form = currentShiftForm
/**
* @see \App\Http\Controllers\CashierShiftController::openShift
 * @see app/Http/Controllers/CashierShiftController.php:86
 * @route '/staff/cashier-shifts/open'
 */
export const openShift = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: openShift.url(options),
    method: 'post',
})

openShift.definition = {
    methods: ["post"],
    url: '/staff/cashier-shifts/open',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CashierShiftController::openShift
 * @see app/Http/Controllers/CashierShiftController.php:86
 * @route '/staff/cashier-shifts/open'
 */
openShift.url = (options?: RouteQueryOptions) => {
    return openShift.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CashierShiftController::openShift
 * @see app/Http/Controllers/CashierShiftController.php:86
 * @route '/staff/cashier-shifts/open'
 */
openShift.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: openShift.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\CashierShiftController::openShift
 * @see app/Http/Controllers/CashierShiftController.php:86
 * @route '/staff/cashier-shifts/open'
 */
    const openShiftForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: openShift.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\CashierShiftController::openShift
 * @see app/Http/Controllers/CashierShiftController.php:86
 * @route '/staff/cashier-shifts/open'
 */
        openShiftForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: openShift.url(options),
            method: 'post',
        })
    
    openShift.form = openShiftForm
/**
* @see \App\Http\Controllers\CashierShiftController::closeShift
 * @see app/Http/Controllers/CashierShiftController.php:119
 * @route '/staff/cashier-shifts/close'
 */
export const closeShift = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: closeShift.url(options),
    method: 'post',
})

closeShift.definition = {
    methods: ["post"],
    url: '/staff/cashier-shifts/close',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CashierShiftController::closeShift
 * @see app/Http/Controllers/CashierShiftController.php:119
 * @route '/staff/cashier-shifts/close'
 */
closeShift.url = (options?: RouteQueryOptions) => {
    return closeShift.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CashierShiftController::closeShift
 * @see app/Http/Controllers/CashierShiftController.php:119
 * @route '/staff/cashier-shifts/close'
 */
closeShift.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: closeShift.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\CashierShiftController::closeShift
 * @see app/Http/Controllers/CashierShiftController.php:119
 * @route '/staff/cashier-shifts/close'
 */
    const closeShiftForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: closeShift.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\CashierShiftController::closeShift
 * @see app/Http/Controllers/CashierShiftController.php:119
 * @route '/staff/cashier-shifts/close'
 */
        closeShiftForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: closeShift.url(options),
            method: 'post',
        })
    
    closeShift.form = closeShiftForm
/**
* @see \App\Http\Controllers\CashierShiftController::printSummary
 * @see app/Http/Controllers/CashierShiftController.php:176
 * @route '/staff/cashier-shifts/{id}/print-summary'
 */
export const printSummary = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printSummary.url(args, options),
    method: 'get',
})

printSummary.definition = {
    methods: ["get","head"],
    url: '/staff/cashier-shifts/{id}/print-summary',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CashierShiftController::printSummary
 * @see app/Http/Controllers/CashierShiftController.php:176
 * @route '/staff/cashier-shifts/{id}/print-summary'
 */
printSummary.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return printSummary.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CashierShiftController::printSummary
 * @see app/Http/Controllers/CashierShiftController.php:176
 * @route '/staff/cashier-shifts/{id}/print-summary'
 */
printSummary.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printSummary.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CashierShiftController::printSummary
 * @see app/Http/Controllers/CashierShiftController.php:176
 * @route '/staff/cashier-shifts/{id}/print-summary'
 */
printSummary.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printSummary.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\CashierShiftController::printSummary
 * @see app/Http/Controllers/CashierShiftController.php:176
 * @route '/staff/cashier-shifts/{id}/print-summary'
 */
    const printSummaryForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: printSummary.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\CashierShiftController::printSummary
 * @see app/Http/Controllers/CashierShiftController.php:176
 * @route '/staff/cashier-shifts/{id}/print-summary'
 */
        printSummaryForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: printSummary.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\CashierShiftController::printSummary
 * @see app/Http/Controllers/CashierShiftController.php:176
 * @route '/staff/cashier-shifts/{id}/print-summary'
 */
        printSummaryForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: printSummary.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    printSummary.form = printSummaryForm
const CashierShiftController = { currentShift, openShift, closeShift, printSummary }

export default CashierShiftController