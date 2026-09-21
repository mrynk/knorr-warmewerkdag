export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    soups: Record<
        string,
        { id: string; name: string; advise: string; video: string }
    >;
    soup_of_the_day: {
        id: string;
        name: string;
        advise: string;
        video: string;
    };
};
