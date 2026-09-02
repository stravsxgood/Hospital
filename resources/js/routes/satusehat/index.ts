import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
    applyUrlDefaults,
} from './../../wayfinder';
/**
 * @see \App\Http\Controllers\SatuSehatController::fhirBundle
 * @see app/Http/Controllers/SatuSehatController.php:26
 * @route '/api/satusehat/records/{medical_record_id}/fhir-bundle'
 */
export const fhirBundle = (
    args:
        | { medical_record_id: string | number }
        | [medical_record_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: fhirBundle.url(args, options),
    method: 'get',
});

fhirBundle.definition = {
    methods: ['get', 'head'],
    url: '/api/satusehat/records/{medical_record_id}/fhir-bundle',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\SatuSehatController::fhirBundle
 * @see app/Http/Controllers/SatuSehatController.php:26
 * @route '/api/satusehat/records/{medical_record_id}/fhir-bundle'
 */
fhirBundle.url = (
    args:
        | { medical_record_id: string | number }
        | [medical_record_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { medical_record_id: args };
    }

    if (Array.isArray(args)) {
        args = {
            medical_record_id: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        medical_record_id: args.medical_record_id,
    };

    return (
        fhirBundle.definition.url
            .replace(
                '{medical_record_id}',
                parsedArgs.medical_record_id.toString(),
            )
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\SatuSehatController::fhirBundle
 * @see app/Http/Controllers/SatuSehatController.php:26
 * @route '/api/satusehat/records/{medical_record_id}/fhir-bundle'
 */
fhirBundle.get = (
    args:
        | { medical_record_id: string | number }
        | [medical_record_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: fhirBundle.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\SatuSehatController::fhirBundle
 * @see app/Http/Controllers/SatuSehatController.php:26
 * @route '/api/satusehat/records/{medical_record_id}/fhir-bundle'
 */
fhirBundle.head = (
    args:
        | { medical_record_id: string | number }
        | [medical_record_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: fhirBundle.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\SatuSehatController::fhirBundle
 * @see app/Http/Controllers/SatuSehatController.php:26
 * @route '/api/satusehat/records/{medical_record_id}/fhir-bundle'
 */
const fhirBundleForm = (
    args:
        | { medical_record_id: string | number }
        | [medical_record_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: fhirBundle.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\SatuSehatController::fhirBundle
 * @see app/Http/Controllers/SatuSehatController.php:26
 * @route '/api/satusehat/records/{medical_record_id}/fhir-bundle'
 */
fhirBundleForm.get = (
    args:
        | { medical_record_id: string | number }
        | [medical_record_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: fhirBundle.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\SatuSehatController::fhirBundle
 * @see app/Http/Controllers/SatuSehatController.php:26
 * @route '/api/satusehat/records/{medical_record_id}/fhir-bundle'
 */
fhirBundleForm.head = (
    args:
        | { medical_record_id: string | number }
        | [medical_record_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: fhirBundle.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

fhirBundle.form = fhirBundleForm;
const satusehat = {
    fhirBundle: Object.assign(fhirBundle, fhirBundle),
};

export default satusehat;
