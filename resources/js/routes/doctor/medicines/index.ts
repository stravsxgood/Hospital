import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\DoctorConsultationController::index
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/doctor/medicines'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/doctor/medicines',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DoctorConsultationController::index
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/doctor/medicines'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorConsultationController::index
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/doctor/medicines'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DoctorConsultationController::index
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/doctor/medicines'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DoctorConsultationController::index
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/doctor/medicines'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DoctorConsultationController::index
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/doctor/medicines'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DoctorConsultationController::index
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/doctor/medicines'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
const medicines = {
    index: Object.assign(index, index),
}

export default medicines