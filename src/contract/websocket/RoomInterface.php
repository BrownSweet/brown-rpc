<?php

namespace brown\contract\websocket;

interface RoomInterface
{
    /**
     * Rooms key
     *
     * @const string
     */
    public const ROOMS_KEY = 'rooms';

    /**
     * Descriptors key
     *
     * @const string
     */
    public const DESCRIPTORS_KEY = 'fds';

    /**
     * Do some init stuffs before workers started.
     *
     * @return RoomInterface
     */
    public function prepare(): RoomInterface;

    /**
     * Add multiple socket fds to a room.
     *
     * @param int fd
     * @param array|string $roomNames
     */
    public function add(int $fd, $roomNames);

    /**
     * Delete multiple socket fds from a room.
     *
     * @param int fd
     * @param array|string $roomNames
     */
    public function delete(int $fd, $roomNames);

    /**
     * Get all sockets by a room key.
     *
     * @param string room
     *
     * @return array
     */
    public function getClients(string $room);

    /**
     * Get all rooms by a fd.
     *
     * @param int fd
     *
     * @return array
     */
    public function getRooms(int $fd);
}
