<?php
namespace App\Services;

use App\Models\Station;
use App\Models\ChargeSession;
use PDO;

class StationService
{

    public function __construct(
        private PDO $pdo
    ) {}

    public function getAllStations(): array
    {
        $req = $this->pdo->query("SELECT id, location_name, power_kw, is_available FROM stations");

        $rows = $req->fetchAll();

        return array_map(fn($r) => new Station(
            (int) $r->id,
            $r->location_name,
            (int) $r->power_kw,
            (bool) $r->is_available
        ), $rows);
    }

    public function getStationById(int $id): ?Station
    {
      $req = $this->pdo->prepare("SELECT id, location_name, power_kw, is_available FROM stations WHERE id = :id");
      $req->bindValue(":id", $id, PDO::PARAM_INT);
      $req->execute();

      $r = $req->fetch();
      if(!$r) return null;

      return new Station(
        (int) $r->id,
        $r->location_name,
        (int) $r->power_kw,
        (bool) $r->is_available
      );
    }

    public function createChargeSession(
      int $stationId,
      string $userName,
      int $kwhRequested
    ): ChargeSession{
      $req = $this->pdo->prepare("INSERT INTO charge_sessions(station_id, user_name, kwh_requested) VALUES(:stationId, :userName, :kwh)");
      
      $req->bindValue(":stationId", $stationId, PDO::PARAM_INT);
      $req->bindValue(":userName", $userName, PDO::PARAM_STR);
      $req->bindValue(":kwh", $kwhRequested, PDO::PARAM_INT);
      $req->execute();

      $id = (int) $this->pdo->lastInsertId();

      $updateReq = $this->pdo->prepare("UPDATE stations SET is_available = 0 WHERE id=:id");
      $updateReq->bindValue(":id", $stationId, PDO::PARAM_INT);
      $updateReq->execute();

      return new ChargeSession(
        $id,
        $stationId,
        $userName,
        $kwhRequested,
        date("Y-m-d H:i:s")
      );

    }

}
