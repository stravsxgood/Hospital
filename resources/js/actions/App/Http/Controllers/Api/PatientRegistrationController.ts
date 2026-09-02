import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\PatientRegistrationController::getAvailableServices
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:17
 * @route '/api/patient/services'
 */
export const getAvailableServices = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getAvailableServices.url(options),
    method: 'get',
})

getAvailableServices.definition = {
    methods: ["get","head"],
    url: '/api/patient/services',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\PatientRegistrationController::getAvailableServices
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:17
 * @route '/api/patient/services'
 */
getAvailableServices.url = (options?: RouteQueryOptions) => {
    return getAvailableServices.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\PatientRegistrationController::getAvailableServices
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:17
 * @route '/api/patient/services'
 */
getAvailableServices.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getAvailableServices.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\PatientRegistrationController::getAvailableServices
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:17
 * @route '/api/patient/services'
 */
getAvailableServices.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getAvailableServices.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\PatientRegistrationController::getAvailableServices
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:17
 * @route '/api/patient/services'
 */
    const getAvailableServicesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getAvailableServices.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\PatientRegistrationController::getAvailableServices
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:17
 * @route '/api/patient/services'
 */
        getAvailableServicesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getAvailableServices.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\PatientRegistrationController::getAvailableServices
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:17
 * @route '/api/patient/services'
 */
        getAvailableServicesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getAvailableServices.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getAvailableServices.form = getAvailableServicesForm
/**
* @see \App\Http\Controllers\Api\PatientRegistrationController::store
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:37
 * @route '/api/patient/registrations'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/patient/registrations',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\PatientRegistrationController::store
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:37
 * @route '/api/patient/registrations'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\PatientRegistrationController::store
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:37
 * @route '/api/patient/registrations'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\PatientRegistrationController::store
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:37
 * @route '/api/patient/registrations'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\PatientRegistrationController::store
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:37
 * @route '/api/patient/registrations'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Api\PatientRegistrationController::myHistory
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:60
 * @route '/api/patient/my-registrations'
 */
export const myHistory = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: myHistory.url(options),
    method: 'get',
})

myHistory.definition = {
    methods: ["get","head"],
    url: '/api/patient/my-registrations',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\PatientRegistrationController::myHistory
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:60
 * @route '/api/patient/my-registrations'
 */
myHistory.url = (options?: RouteQueryOptions) => {
    return myHistory.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\PatientRegistrationController::myHistory
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:60
 * @route '/api/patient/my-registrations'
 */
myHistory.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: myHistory.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\PatientRegistrationController::myHistory
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:60
 * @route '/api/patient/my-registrations'
 */
myHistory.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: myHistory.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\PatientRegistrationController::myHistory
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:60
 * @route '/api/patient/my-registrations'
 */
    const myHistoryForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: myHistory.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\PatientRegistrationController::myHistory
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:60
 * @route '/api/patient/my-registrations'
 */
        myHistoryForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: myHistory.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\PatientRegistrationController::myHistory
 * @see app/Http/Controllers/Api/PatientRegistrationController.php:60
 * @route '/api/patient/my-registrations'
 */
        myHistoryForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: myHistory.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    myHistory.form = myHistoryForm
const PatientRegistrationController = { getAvailableServices, store, myHistory }

export default PatientRegistrationController