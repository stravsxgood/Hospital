import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
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
const reservations = {
    confirmArrival: Object.assign(confirmArrival, confirmArrival),
}

export default reservations