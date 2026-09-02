import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/api/doctor/consultations'
 */
const storec1cb7ac395c6d5fd6715547390872a1b = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storec1cb7ac395c6d5fd6715547390872a1b.url(options),
    method: 'post',
})

storec1cb7ac395c6d5fd6715547390872a1b.definition = {
    methods: ["post"],
    url: '/api/doctor/consultations',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/api/doctor/consultations'
 */
storec1cb7ac395c6d5fd6715547390872a1b.url = (options?: RouteQueryOptions) => {
    return storec1cb7ac395c6d5fd6715547390872a1b.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/api/doctor/consultations'
 */
storec1cb7ac395c6d5fd6715547390872a1b.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storec1cb7ac395c6d5fd6715547390872a1b.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/api/doctor/consultations'
 */
    const storec1cb7ac395c6d5fd6715547390872a1bForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storec1cb7ac395c6d5fd6715547390872a1b.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/api/doctor/consultations'
 */
        storec1cb7ac395c6d5fd6715547390872a1bForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storec1cb7ac395c6d5fd6715547390872a1b.url(options),
            method: 'post',
        })
    
    storec1cb7ac395c6d5fd6715547390872a1b.form = storec1cb7ac395c6d5fd6715547390872a1bForm
    /**
* @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/doctor/consultations'
 */
const storee2422ba453f66d11e9adf3d05ed313a5 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storee2422ba453f66d11e9adf3d05ed313a5.url(options),
    method: 'post',
})

storee2422ba453f66d11e9adf3d05ed313a5.definition = {
    methods: ["post"],
    url: '/doctor/consultations',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/doctor/consultations'
 */
storee2422ba453f66d11e9adf3d05ed313a5.url = (options?: RouteQueryOptions) => {
    return storee2422ba453f66d11e9adf3d05ed313a5.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/doctor/consultations'
 */
storee2422ba453f66d11e9adf3d05ed313a5.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storee2422ba453f66d11e9adf3d05ed313a5.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/doctor/consultations'
 */
    const storee2422ba453f66d11e9adf3d05ed313a5Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storee2422ba453f66d11e9adf3d05ed313a5.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/doctor/consultations'
 */
        storee2422ba453f66d11e9adf3d05ed313a5Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storee2422ba453f66d11e9adf3d05ed313a5.url(options),
            method: 'post',
        })
    
    storee2422ba453f66d11e9adf3d05ed313a5.form = storee2422ba453f66d11e9adf3d05ed313a5Form

/**
* Multiple routes resolve to \App\Http\Controllers\DoctorConsultationController::store, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `store['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const store = {
    '/api/doctor/consultations': storec1cb7ac395c6d5fd6715547390872a1b,
    '/doctor/consultations': storee2422ba453f66d11e9adf3d05ed313a5,
}

/**
* @see \App\Http\Controllers\DoctorConsultationController::getPatientHistory
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/api/doctor/patients/{id}/history'
 */
const getPatientHistory1aac0ee0f535ae2807525151517865f0 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getPatientHistory1aac0ee0f535ae2807525151517865f0.url(args, options),
    method: 'get',
})

getPatientHistory1aac0ee0f535ae2807525151517865f0.definition = {
    methods: ["get","head"],
    url: '/api/doctor/patients/{id}/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DoctorConsultationController::getPatientHistory
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/api/doctor/patients/{id}/history'
 */
getPatientHistory1aac0ee0f535ae2807525151517865f0.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return getPatientHistory1aac0ee0f535ae2807525151517865f0.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorConsultationController::getPatientHistory
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/api/doctor/patients/{id}/history'
 */
getPatientHistory1aac0ee0f535ae2807525151517865f0.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getPatientHistory1aac0ee0f535ae2807525151517865f0.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DoctorConsultationController::getPatientHistory
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/api/doctor/patients/{id}/history'
 */
getPatientHistory1aac0ee0f535ae2807525151517865f0.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getPatientHistory1aac0ee0f535ae2807525151517865f0.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DoctorConsultationController::getPatientHistory
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/api/doctor/patients/{id}/history'
 */
    const getPatientHistory1aac0ee0f535ae2807525151517865f0Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getPatientHistory1aac0ee0f535ae2807525151517865f0.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DoctorConsultationController::getPatientHistory
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/api/doctor/patients/{id}/history'
 */
        getPatientHistory1aac0ee0f535ae2807525151517865f0Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getPatientHistory1aac0ee0f535ae2807525151517865f0.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DoctorConsultationController::getPatientHistory
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/api/doctor/patients/{id}/history'
 */
        getPatientHistory1aac0ee0f535ae2807525151517865f0Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getPatientHistory1aac0ee0f535ae2807525151517865f0.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getPatientHistory1aac0ee0f535ae2807525151517865f0.form = getPatientHistory1aac0ee0f535ae2807525151517865f0Form
    /**
* @see \App\Http\Controllers\DoctorConsultationController::getPatientHistory
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/doctor/patients/{patient_id}/history'
 */
const getPatientHistory98499e2973ec031077b446bce2e6f933 = (args: { patient_id: string | number } | [patient_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getPatientHistory98499e2973ec031077b446bce2e6f933.url(args, options),
    method: 'get',
})

getPatientHistory98499e2973ec031077b446bce2e6f933.definition = {
    methods: ["get","head"],
    url: '/doctor/patients/{patient_id}/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DoctorConsultationController::getPatientHistory
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/doctor/patients/{patient_id}/history'
 */
getPatientHistory98499e2973ec031077b446bce2e6f933.url = (args: { patient_id: string | number } | [patient_id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { patient_id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    patient_id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        patient_id: args.patient_id,
                }

    return getPatientHistory98499e2973ec031077b446bce2e6f933.definition.url
            .replace('{patient_id}', parsedArgs.patient_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorConsultationController::getPatientHistory
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/doctor/patients/{patient_id}/history'
 */
getPatientHistory98499e2973ec031077b446bce2e6f933.get = (args: { patient_id: string | number } | [patient_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getPatientHistory98499e2973ec031077b446bce2e6f933.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DoctorConsultationController::getPatientHistory
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/doctor/patients/{patient_id}/history'
 */
getPatientHistory98499e2973ec031077b446bce2e6f933.head = (args: { patient_id: string | number } | [patient_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getPatientHistory98499e2973ec031077b446bce2e6f933.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DoctorConsultationController::getPatientHistory
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/doctor/patients/{patient_id}/history'
 */
    const getPatientHistory98499e2973ec031077b446bce2e6f933Form = (args: { patient_id: string | number } | [patient_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getPatientHistory98499e2973ec031077b446bce2e6f933.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DoctorConsultationController::getPatientHistory
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/doctor/patients/{patient_id}/history'
 */
        getPatientHistory98499e2973ec031077b446bce2e6f933Form.get = (args: { patient_id: string | number } | [patient_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getPatientHistory98499e2973ec031077b446bce2e6f933.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DoctorConsultationController::getPatientHistory
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/doctor/patients/{patient_id}/history'
 */
        getPatientHistory98499e2973ec031077b446bce2e6f933Form.head = (args: { patient_id: string | number } | [patient_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getPatientHistory98499e2973ec031077b446bce2e6f933.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getPatientHistory98499e2973ec031077b446bce2e6f933.form = getPatientHistory98499e2973ec031077b446bce2e6f933Form

/**
* Multiple routes resolve to \App\Http\Controllers\DoctorConsultationController::getPatientHistory, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `getPatientHistory['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const getPatientHistory = {
    '/api/doctor/patients/{id}/history': getPatientHistory1aac0ee0f535ae2807525151517865f0,
    '/doctor/patients/{patient_id}/history': getPatientHistory98499e2973ec031077b446bce2e6f933,
}

/**
* @see \App\Http\Controllers\DoctorConsultationController::getMedicines
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/api/doctor/medicines'
 */
const getMedicines093b353ab8db209dd26c7cd78d49da91 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getMedicines093b353ab8db209dd26c7cd78d49da91.url(options),
    method: 'get',
})

getMedicines093b353ab8db209dd26c7cd78d49da91.definition = {
    methods: ["get","head"],
    url: '/api/doctor/medicines',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DoctorConsultationController::getMedicines
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/api/doctor/medicines'
 */
getMedicines093b353ab8db209dd26c7cd78d49da91.url = (options?: RouteQueryOptions) => {
    return getMedicines093b353ab8db209dd26c7cd78d49da91.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorConsultationController::getMedicines
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/api/doctor/medicines'
 */
getMedicines093b353ab8db209dd26c7cd78d49da91.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getMedicines093b353ab8db209dd26c7cd78d49da91.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DoctorConsultationController::getMedicines
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/api/doctor/medicines'
 */
getMedicines093b353ab8db209dd26c7cd78d49da91.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getMedicines093b353ab8db209dd26c7cd78d49da91.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DoctorConsultationController::getMedicines
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/api/doctor/medicines'
 */
    const getMedicines093b353ab8db209dd26c7cd78d49da91Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getMedicines093b353ab8db209dd26c7cd78d49da91.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DoctorConsultationController::getMedicines
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/api/doctor/medicines'
 */
        getMedicines093b353ab8db209dd26c7cd78d49da91Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getMedicines093b353ab8db209dd26c7cd78d49da91.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DoctorConsultationController::getMedicines
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/api/doctor/medicines'
 */
        getMedicines093b353ab8db209dd26c7cd78d49da91Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getMedicines093b353ab8db209dd26c7cd78d49da91.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getMedicines093b353ab8db209dd26c7cd78d49da91.form = getMedicines093b353ab8db209dd26c7cd78d49da91Form
    /**
* @see \App\Http\Controllers\DoctorConsultationController::getMedicines
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/doctor/medicines'
 */
const getMedicinesdab86cb79f327ab5a05575fa55e5d726 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getMedicinesdab86cb79f327ab5a05575fa55e5d726.url(options),
    method: 'get',
})

getMedicinesdab86cb79f327ab5a05575fa55e5d726.definition = {
    methods: ["get","head"],
    url: '/doctor/medicines',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DoctorConsultationController::getMedicines
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/doctor/medicines'
 */
getMedicinesdab86cb79f327ab5a05575fa55e5d726.url = (options?: RouteQueryOptions) => {
    return getMedicinesdab86cb79f327ab5a05575fa55e5d726.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorConsultationController::getMedicines
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/doctor/medicines'
 */
getMedicinesdab86cb79f327ab5a05575fa55e5d726.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getMedicinesdab86cb79f327ab5a05575fa55e5d726.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DoctorConsultationController::getMedicines
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/doctor/medicines'
 */
getMedicinesdab86cb79f327ab5a05575fa55e5d726.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getMedicinesdab86cb79f327ab5a05575fa55e5d726.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DoctorConsultationController::getMedicines
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/doctor/medicines'
 */
    const getMedicinesdab86cb79f327ab5a05575fa55e5d726Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getMedicinesdab86cb79f327ab5a05575fa55e5d726.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DoctorConsultationController::getMedicines
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/doctor/medicines'
 */
        getMedicinesdab86cb79f327ab5a05575fa55e5d726Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getMedicinesdab86cb79f327ab5a05575fa55e5d726.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DoctorConsultationController::getMedicines
 * @see app/Http/Controllers/DoctorConsultationController.php:258
 * @route '/doctor/medicines'
 */
        getMedicinesdab86cb79f327ab5a05575fa55e5d726Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getMedicinesdab86cb79f327ab5a05575fa55e5d726.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getMedicinesdab86cb79f327ab5a05575fa55e5d726.form = getMedicinesdab86cb79f327ab5a05575fa55e5d726Form

/**
* Multiple routes resolve to \App\Http\Controllers\DoctorConsultationController::getMedicines, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `getMedicines['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const getMedicines = {
    '/api/doctor/medicines': getMedicines093b353ab8db209dd26c7cd78d49da91,
    '/doctor/medicines': getMedicinesdab86cb79f327ab5a05575fa55e5d726,
}

const DoctorConsultationController = { store, getPatientHistory, getMedicines }

export default DoctorConsultationController