export default interface Entry {
    code: string;
    name: string;
    soup: string;
    masked_email: string;
    reward?: { name: string; description: string };
}
