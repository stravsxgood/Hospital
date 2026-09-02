import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\StaffActionController::confirmArrival
 * @see app/Http/Controllers/StaffActionController.php:37
 * @route '/staff/reservations/{id}/confirm-arrival'
 */
export const confirmArrival = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmArrival.url(args, options),
    method: 'post',
})

confirmArrival.definition = {
    methods: ["post"],
    url: '/staff/reservations/{id}/confirm-arrival',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\StaffActionController::confirmArrival
 * @see app/Http/Controllers/StaffActionController.php:37
 * @route '/staff/reservations/{id}/confirm-arrival'
 */
confirmArrival.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return confirmArrival.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\StaffActionController::confirmArrival
 * @see app/Http/Controllers/StaffActionController.php:37
 * @route '/staff/reservations/{id}/confirm-arrival'
 */
confirmArrival.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmArrival.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\StaffActionController::confirmArrival
 * @see app/Http/Controllers/StaffActionController.php:37
 * @route '/staff/reservations/{id}/confirm-arrival'
 */
    const confirmArrivalForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: confirmArrival.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\StaffActionController::confirmArrival
 * @see app/Http/Controllers/StaffActionController.php:37
 * @route '/staff/reservations/{id}/confirm-arrival'
 */
        confirmArrivalForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: confirmArrival.url(args, options),
            method: 'post',
        })
    
    confirmArrival.form = confirmArrivalForm
/**
* @see \App\Http\Controllers\StaffActionController::processPrescription
 * @see app/Http/Controllers/StaffActionController.php:104
 * @route '/staff/prescriptions/{id}/process'
 */
export const processPrescription = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: processPrescription.url(args, options),
    method: 'post',
})

processPrescription.definition = {
    methods: ["post"],
    url: '/staff/prescriptions/{id}/process',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\StaffActionController::processPrescription
 * @see app/Http/Controllers/StaffActionController.php:104
 * @route '/staff/prescriptions/{id}/process'
 */
processPrescription.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return processPrescription.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\StaffActionController::processPrescription
 * @see app/Http/Controllers/StaffActionController.php:104
 * @route '/staff/prescriptions/{id}/process'
 */
processPrescription.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: processPrescription.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\StaffActionController::processPrescription
 * @see app/Http/Controllers/StaffActionController.php:104
 * @route '/staff/prescriptions/{id}/process'
 */
    const processPrescriptionForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: processPrescription.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\StaffActionController::processPrescription
 * @see app/Http/Controllers/StaffActionController.php:104
 * @route '/staff/prescriptions/{id}/process'
 */
        processPrescriptionForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: processPrescription.url(args, options),
            method: 'post',
        })
    
    processPrescription.form = processPrescriptionForm
/**
* @see \App\Http\Controllers\StaffActionController::completePrescription
 * @see app/Http/Controllers/StaffActionController.php:143
 * @route '/staff/prescriptions/{id}/complete'
 */
export const completePrescription = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completePrescription.url(args, options),
    method: 'post',
})

completePrescription.definition = {
    methods: ["post"],
    url: '/staff/prescriptions/{id}/complete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\StaffActionController::completePrescription
 * @see app/Http/Controllers/StaffActionController.php:143
 * @route '/staff/prescriptions/{id}/complete'
 */
completePrescription.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return completePrescription.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\StaffActionController::completePrescription
 * @see app/Http/Controllers/StaffActionController.php:143
 * @route '/staff/prescriptions/{id}/complete'
 */
completePrescription.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completePrescription.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\StaffActionController::completePrescription
 * @see app/Http/Controllers/StaffActionController.php:143
 * @route '/staff/prescriptions/{id}/complete'
 */
    const completePrescriptionForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: completePrescription.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\StaffActionController::completePrescription
 * @see app/Http/Controllers/StaffActionController.php:143
 * @route '/staff/prescriptions/{id}/complete'
 */
        completePrescriptionForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: completePrescription.url(args, options),
            method: 'post',
        })
    
    completePrescription.form = completePrescriptionForm
const StaffActionController = { confirmArrival, processPrescription, completePrescription }

export default StaffActionController