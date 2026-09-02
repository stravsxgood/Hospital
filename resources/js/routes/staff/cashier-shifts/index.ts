import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\CashierShiftController::current
 * @see app/Http/Controllers/CashierShiftController.php:30
 * @route '/staff/cashier-shifts/current'
 */
export const current = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: current.url(options),
    method: 'get',
})

current.definition = {
    methods: ["get","head"],
    url: '/staff/cashier-shifts/current',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CashierShiftController::current
 * @see app/Http/Controllers/CashierShiftController.php:30
 * @route '/staff/cashier-shifts/current'
 */
current.url = (options?: RouteQueryOptions) => {
    return current.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CashierShiftController::current
 * @see app/Http/Controllers/CashierShiftController.php:30
 * @route '/staff/cashier-shifts/current'
 */
current.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: current.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CashierShiftController::current
 * @see app/Http/Controllers/CashierShiftController.php:30
 * @route '/staff/cashier-shifts/current'
 */
current.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: current.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\CashierShiftController::current
 * @see app/Http/Controllers/CashierShiftController.php:30
 * @route '/staff/cashier-shifts/current'
 */
    const currentForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: current.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\CashierShiftController::current
 * @see app/Http/Controllers/CashierShiftController.php:30
 * @route '/staff/cashier-shifts/current'
 */
        currentForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: current.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\CashierShiftController::current
 * @see app/Http/Controllers/CashierShiftController.php:30
 * @route '/staff/cashier-shifts/current'
 */
        currentForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: current.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    current.form = currentForm
/**
* @see \App\Http\Controllers\CashierShiftController::open
 * @see app/Http/Controllers/CashierShiftController.php:86
 * @route '/staff/cashier-shifts/open'
 */
export const open = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: open.url(options),
    method: 'post',
})

open.definition = {
    methods: ["post"],
    url: '/staff/cashier-shifts/open',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CashierShiftController::open
 * @see app/Http/Controllers/CashierShiftController.php:86
 * @route '/staff/cashier-shifts/open'
 */
open.url = (options?: RouteQueryOptions) => {
    return open.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CashierShiftController::open
 * @see app/Http/Controllers/CashierShiftController.php:86
 * @route '/staff/cashier-shifts/open'
 */
open.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: open.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\CashierShiftController::open
 * @see app/Http/Controllers/CashierShiftController.php:86
 * @route '/staff/cashier-shifts/open'
 */
    const openForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: open.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\CashierShiftController::open
 * @see app/Http/Controllers/CashierShiftController.php:86
 * @route '/staff/cashier-shifts/open'
 */
        openForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: open.url(options),
            method: 'post',
        })
    
    open.form = openForm
/**
* @see \App\Http\Controllers\CashierShiftController::close
 * @see app/Http/Controllers/CashierShiftController.php:119
 * @route '/staff/cashier-shifts/close'
 */
export const close = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: close.url(options),
    method: 'post',
})

close.definition = {
    methods: ["post"],
    url: '/staff/cashier-shifts/close',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CashierShiftController::close
 * @see app/Http/Controllers/CashierShiftController.php:119
 * @route '/staff/cashier-shifts/close'
 */
close.url = (options?: RouteQueryOptions) => {
    return close.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CashierShiftController::close
 * @see app/Http/Controllers/CashierShiftController.php:119
 * @route '/staff/cashier-shifts/close'
 */
close.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: close.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\CashierShiftController::close
 * @see app/Http/Controllers/CashierShiftController.php:119
 * @route '/staff/cashier-shifts/close'
 */
    const closeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: close.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\CashierShiftController::close
 * @see app/Http/Controllers/CashierShiftController.php:119
 * @route '/staff/cashier-shifts/close'
 */
        closeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: close.url(options),
            method: 'post',
        })
    
    close.form = closeForm
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
const cashierShifts = {
    current: Object.assign(current, current),
open: Object.assign(open, open),
close: Object.assign(close, close),
printSummary: Object.assign(printSummary, printSummary),
}

export default cashierShifts