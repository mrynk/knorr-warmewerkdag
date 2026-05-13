export default interface Entry {
    id: number;
    code: string;
    masked_email: string;
    reward?: { name: string };
}