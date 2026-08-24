export interface Specialization {
    id?: number;
    specialization_id?: number;
    code_specialization?: string;
    name?: string;
    name_specialization?: string;
    description?: string;
}

export interface Doctor {
    id?: number;
    doctor_id?: number;
    user_id?: number;
    specialization_id?: number;
    name: string;
    sip_number?: string;
    gender?: string;
    number_phone?: string;
    email?: string | null;
    alamat?: string | null;
    join_date?: string;
    status?: string;
    specialization?: Specialization | string;
}

export interface Poli {
    id?: number;
    poli_id?: number;
    kode_poli?: string;
    name?: string;
    name_poli?: string;
    location?: string;
    status?: string;
}

export interface Room {
    id?: number;
    room_id?: number;
    code_room?: string;
    name?: string;
    name_room?: string;
    type_room?: string;
    capacity?: number;
    floor?: number;
}

export interface DoctorSchedule {
    id?: number;
    doctor_schedule_id?: number;
    doctor_id?: number;
    poli_id?: number;
    room_id?: number;
    day?: string;
    day_of_week?: string;
    start_time: string;
    end_time: string;
    quota?: number;
    quota_day?: number;
    status?: boolean | string;
    created_at?: string;
    updated_at?: string;
    doctor?: Doctor;
    poli?: Poli;
    room?: Room;
}

export interface PatientStory {
    id: number | string;
    title: string;
    excerpt: string;
    full_content: string;
    patient_name: string;
    patient_age?: string | number;
    diagnosis: string;
    doctor_name: string;
    poli_name: string;
    category: string;
    read_time: string;
    published_at: string;
    quote?: string;
    badge?: string;
    image_url?: string;
}

export interface ConditionTreated {
    title: string;
    category: string;
    severity: string;
    desc: string;
    symptoms: string[];
}

export interface MedicalProcedure {
    title: string;
    category: string;
    duration: string;
    desc: string;
    benefits: string[];
}

export interface SpecializationMetric {
    value: string;
    label: string;
    desc: string;
}

export interface SpecializationFaq {
    q: string;
    a: string;
}

export interface SpecializationDetail {
    slug: string;
    name: string;
    short_name: string;
    category: string;
    icon_name: string;
    badge: string;
    tagline: string;
    description: string;
    quote?: string;
    metrics: SpecializationMetric[];
    conditions: ConditionTreated[];
    procedures: MedicalProcedure[];
    faqs: SpecializationFaq[];
}

export interface SpecializationTabItem {
    slug: string;
    name: string;
    short_name: string;
    category: string;
    icon: string;
    badge: string;
}

export interface PoliRoomInfo {
    name: string;
    code: string;
    desc: string;
}

export interface PoliDoctorMember {
    name: string;
    role: string;
    specialty: string;
    sip: string;
    experience: string;
    schedule: string;
    badge: string;
}

export interface PoliNurseMember {
    name: string;
    role: string;
    str: string;
    cert: string;
}

export interface PoliFaq {
    q: string;
    a: string;
}

export interface PoliTeamDetail {
    code: string;
    slug: string;
    name: string;
    short_name: string;
    badge: string;
    tagline: string;
    icon_name: string;
    floor: string;
    head_doctor: string;
    head_doctor_title: string;
    head_nurse: string;
    head_nurse_title: string;
    description: string;
    operating_hours: string;
    metrics: { value: string; label: string; desc: string }[];
    rooms: PoliRoomInfo[];
    scope_services: string[];
    team_doctors: PoliDoctorMember[];
    team_nurses: PoliNurseMember[];
    faqs: PoliFaq[];
}

export interface PoliTabItem {
    code: string;
    slug: string;
    name: string;
    short_name: string;
    icon: string;
    floor: string;
    badge: string;
}

export interface ClinicBranchItem {
    id: number | string;
    name: string;
    slug: string;
    facility_type: string;
    category_badge: string;
    city: string;
    province: string;
    address: string;
    distance_info: string;
    operating_hours: string;
    phone: string;
    whatsapp: string;
    emergency_24h: boolean;
    bed_capacity: number;
    doctor_count: number;
    google_maps_url: string;
    available_polis: string[];
    featured_facilities: string[];
    image_url: string;
}

export interface ClinicLocationProps {
    clinics: ClinicBranchItem[];
    cities: string[];
    facilityTypes: string[];
}

export interface VitalSigns {
    systolic?: number | string | null;
    diastolic?: number | string | null;
    pulse?: number | string | null;
    temperature?: number | string | null;
    respiratory_rate?: number | string | null;
    weight?: number | string | null;
    height?: number | string | null;
    bmi?: number | string | null;
    oxygen_saturation?: number | string | null;
}

export interface Medicine {
    medicine_id: number;
    code_medicine: string;
    name_medicine: string;
    type: string;
    stock: number;
    unit: string;
    price: number | string;
}

export interface PrescriptionItem {
    prescription_item_id?: number;
    prescription_id?: number;
    medicine_id: number;
    quantity: number;
    dosage: string;
    instructions: string;
    notes?: string | null;
    medicine?: Medicine;
}

export interface Prescription {
    prescription_id?: number;
    medical_record_id?: number;
    prescription_number: string;
    status: 'menunggu' | 'diproses' | 'selesai' | string;
    notes?: string | null;
    items?: PrescriptionItem[];
    prescription_items?: PrescriptionItem[];
    created_at?: string;
    updated_at?: string;
}

export interface MedicalRecord {
    medical_record_id?: number;
    reservation_id?: number | null;
    patient_id: number;
    doctor_id: number;
    subjective: string;
    objective: VitalSigns | Record<string, any>;
    assessment: string;
    plan: string;
    physical_check?: string | null;
    created_at?: string;
    updated_at?: string;
    doctor?: Doctor;
    patient?: any;
    prescription?: Prescription | null;
    reservation?: any;
}

export interface Nurse {
    nurse_id: number;
    user_id?: number;
    name: string;
    registration_number: string;
    type: 'tetap' | 'koas' | string;
    institute?: string | null;
    gender?: string | null;
    date_start?: string | null;
    date_end?: string | null;
    is_tetap?: boolean;
    is_koas?: boolean;
}

export interface BillingItem {
    billing_item_id?: number;
    billing_id?: number;
    item_type: 'consultation_fee' | 'medicine' | 'procedure' | string;
    item_name: string;
    quantity: number;
    unit_price: number | string;
    subtotal: number | string;
    created_at?: string;
    updated_at?: string;
}

export interface Billing {
    billing_id: number;
    reservation_id: number;
    patient_id: number;
    processed_by_nurse_id?: number | null;
    invoice_number: string;
    total_amount: number | string;
    status: 'unpaid' | 'pending' | 'paid' | 'expired' | 'cancelled' | string;
    payment_method?: 'cash' | 'xendit_invoice' | 'xendit_qris' | string | null;
    xendit_id?: string | null;
    xendit_payment_url?: string | null;
    paid_at?: string | null;
    created_at?: string;
    updated_at?: string;
    patient?: any;
    reservation?: any;
    appointment?: any;
    processed_by_nurse?: Nurse | null;
    items?: BillingItem[];
}


