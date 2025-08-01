import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import Stack from "@mui/material/Stack";
import { styled } from "@mui/material/styles";
import Paper from "@mui/material/Paper";
import {
    Button,
    Dialog,
    Input,
    DialogTitle,
    DialogContent,
} from "@mui/material";
import { useState } from "react";
import axios from "axios";

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
    const [playlistType, setPlaylistType] = useState("");
    const [tracks, setTracks] = useState([]);
    const [open, setOpen] = useState(false);

    const getPlaylistTracks = (type) => {
        axios.get(`/spotify/preview_playlist/${type}`).then((response) => {
            setTracks(response.data);
            setOpen(true);
        });
    };

    const createPlaylist = (type) => {
        axios
            .get(`/spotify/create_playlist/${type}`)
            .then((response) => {
                console.log("Playlist created", response.data);
                setOpen(false);
            })
            .catch((error) => {
                console.error(
                    "There was an error creating the playlist!",
                    error
                );
            });
    };

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
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            {/* {auth.user.spotify_access_token != null} ? ( */}
                            <Stack spacing={1}>
                                {auth.expired && (
                                    <Item>
                                        <div>
                                            <div className="text-red-500 mb-3">
                                                Your Spotify token is expired.
                                                Please login again.
                                            </div>
                                            <a
                                                href="/spotify/user_auth"
                                                className="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                            >
                                                Login with Spotify
                                            </a>
                                        </div>
                                    </Item>
                                )}
                                <Item>
                                    <a
                                        href={"/spotify/get_saved_tracks"}
                                        className="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Get Saved Tracks
                                    </a>
                                </Item>
                                <Item>
                                    <a
                                        href={"/spotify/get_playlists"}
                                        className="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Get Playlists
                                    </a>
                                </Item>
                                <Item>
                                    <Input
                                        placeholder="Enter playlist type"
                                        className="mb-2 mr-2"
                                        value={playlistType}
                                        onChange={(e) =>
                                            setPlaylistType(e.target.value)
                                        }
                                    />
                                    <Button
                                        onClick={() =>
                                            getPlaylistTracks(playlistType)
                                        }
                                        className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Create Playlist
                                    </Button>
                                </Item>
                            </Stack>
                        </div>
                    </div>
                </div>
            </div>
            <Dialog open={open} onClose={() => setOpen(false)} fullWidth>
                <DialogTitle>Create Playlist</DialogTitle>
                <DialogContent>
                    {tracks.map((track) => (
                        <div key={track.id} className="mb-2">
                            <span>{track.name}</span>
                            <span className="text-gray-500">
                                {" by " + track.artist_name}
                            </span>
                        </div>
                    ))}

                    <Button
                        onClick={() => createPlaylist(playlistType)}
                        variant="contained"
                        color="primary"
                    >
                        Create Playlist
                    </Button>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
