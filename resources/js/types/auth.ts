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
    roles: string[];
};
