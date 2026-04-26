export type Company = {
    id: number;
    name: string;
};

export type Branch = {
    id: number;
    name: string;
    company_id: number;
};

export type Employee = {
    id: number;
    name: string;
    company_id: number;
    branch_id: number;
    user_id: number | null;
};

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    employees?: Employee[];
    [key: string]: unknown;
};

export type Auth = {
    user: User;
    companies: Company[];
    selectedCompanyId: number | null;
    branches: Branch[];
    roles: string[];
    unreadNotificationsCount: number;
};

export type CommissionNoteAudit = {
    id: number;
    commission_note_id: number | null;
    user_id: number;
    event: 'created' | 'updated' | 'deleted';
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
    ip_address: string | null;
    created_at: string;
    actor?: Pick<User, 'id' | 'name'>;
};

export type CommissionNote = {
    id: number;
    company_id: number;
    branch_id: number;
    employee_id: number;
    created_by: number;
    amount: string; // decimal cast serialises as string
    description: string | null;
    payment_date: string;
    employee?: Employee;
    author?: Pick<User, 'id' | 'name'>;
    audits?: CommissionNoteAudit[];
};

export type AppNotification = {
    id: string;
    message: string;
    note_id: number | null;
    amount: string | null;
    payment_date: string | null;
    branch: string | null;
    read_at: string | null;
    created_at: string;
};

export type BranchStat = {
    id: number;
    name: string;
    total_this_month: number;
    total_all_time: number;
};

export type EmployeeStat = {
    id: number;
    name: string;
    total_commission: number;
    note_count: number;
};

export type RecentNote = {
    id: number;
    amount: string;
    payment_date: string | null;
    description: string | null;
    employee_name: string | null;
    branch_name: string | null;
    author_name: string | null;
};
