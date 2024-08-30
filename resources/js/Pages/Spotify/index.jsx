import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import Stack from "@mui/material/Stack";
import { styled } from "@mui/material/styles";
import Paper from "@mui/material/Paper";

const Item = styled(Paper)(({ theme }) => ({
    backgroundColor: "#fff",
    ...theme.typography.body2,
    padding: theme.spacing(1),
    textAlign: "center",
    color: theme.palette.text.secondary,
    ...theme.applyStyles("dark", {
        backgroundColor: "#1A2027",
    }),
}));

export default function SpotifyIndex({ auth }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Spotify
                </h2>
            }
        >
            <Head title="Spotify" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            {/* {auth.user.spotify_access_token != null} ? ( */}
                            <Stack spacing={1}>
                                <Item>
                                    {" "}
                                    <div>
                                        <div class="text-red-500">
                                            Your Spotify token is expired.
                                            Please login again.
                                        </div>
                                        <a
                                            href="/spotify/user_auth"
                                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                        >
                                            Login with Spotify
                                        </a>
                                    </div>
                                </Item>
                                <Item>
                                    <a
                                        href="/spotify/get_saved_tracks/<?= Auth::user()->spotify_access_token ?>"
                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Get Saved Tracks
                                    </a>
                                </Item>
                            </Stack>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
