package main

import (
	"app/pb"
	"context"
	"fmt"
	"log"
	"net"
	"os/exec"
	"time"

	"google.golang.org/grpc"
)

type server struct {
	pb.UnimplementedLiveDataServiceServer
	ip   string
	port string
}

func (s *server) StreamLiveData(req *pb.LiveDataRequest, stream pb.LiveDataService_StreamLiveDataServer) error {
	for {
		liveData := &pb.LiveData{
			Timestamp: time.Now().Format(time.RFC3339),
			Message:   fmt.Sprintf("Live update from Helios at %s", time.Now().Format("15:04:05")),
			Type:      "update",
		}
		if err := stream.Send(liveData); err != nil {
			return err
		}
		time.Sleep(1 * time.Second)
	}
}

func (s *server) CheckHealth(ctx context.Context, req *pb.HealthCheckRequest) (*pb.HealthCheckResponse, error) {
	ip := req.Ip
	port := req.Port

	if ip == "" {
		ip = s.ip
	}
	if port == "" {
		port = s.port
	}

	err := healthCheck(ip, port)
	if err != nil {
		return &pb.HealthCheckResponse{Status: "unhealthy"}, nil
	}
	return &pb.HealthCheckResponse{Status: "healthy"}, nil
}

func healthCheck(ip string, port string) error {
	cmd := exec.Command("sh", "-c", "nc -zv "+ip+" "+port)
	output, err := cmd.CombinedOutput()
	if err != nil {
		log.Printf("Health check failed: %v, output: %s", err, output)
		return fmt.Errorf("health check failed: %v", err)
	}

	log.Printf("Health check succeeded: output: %s", output)
	return nil
}

func main() {
	ip := "0.0.0.0"
	port := "50051"

	addr := fmt.Sprintf("%s:%s", ip, port)
	lis, err := net.Listen("tcp", addr)
	if err != nil {
		log.Fatalf("failed to listen on %s: %v", addr, err)
	}

	s := grpc.NewServer()
	pb.RegisterLiveDataServiceServer(s, &server{ip: ip, port: port})
	log.Printf("gRPC server listening on %s...", addr)
	if err := s.Serve(lis); err != nil {
		log.Fatalf("failed to serve: %v", err)
	}
}
